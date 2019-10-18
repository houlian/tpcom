<?php

namespace app\index\action;

use think\Db;
use think\Cache;

class RedAction{

	static $statusArr = ['0'=>'选择状态','1'=>'未领取','2'=>'已领取','3'=>'已使用','4'=>'已失效'];

	/**
	 * 优惠券配置
	 * @return [type] [description]
	 */
	public function redconf(){
		$table = "basesetting";
		$name = "redconf";
		$d = Db::query("select * from `{$table}` where name=?",[$name]);
		if (!empty($d[0])) {
			$conf = $d[0]['content'];
			$conf = json_decode($conf, true);
		}else{
			$conf = ['num'=>'50&50', 'am_s'=>'9:30:00','am_e'=>'10:00:00','pm_s'=>'15:00:00','pm_e'=>'16:00:00','redcancel'=>'17:00:00'];
		}
		return $conf;
	}

	/**
	 * 读取配置生成优惠券
	 * 分上午和下午
	 */
	public function createRedByConfig(){
		$conf = $this->redconf();
		$num = $conf['num'];
		$redcancel = $conf['redcancel'];
		$nums = explode("&", $num);
		$hour = date("H");
		if ($hour < 12) {
			$count = $nums[0];
		}else{
			$count = $nums[1];
		}
		$relnum = $this->createred($count, $redcancel);
		return $relnum;
	}

	/**
	 * 生成指定数目的优惠券
	 * @param  int
	 * @param  redcancel 过期时间
	 * @return [type]
	 */
	public function createred($num, $redcancel = '17:00:00'){
		if ($num <= 0) {
			return false;
		}
		$length = strlen($num);

		$now = time();
		$date = date("Y-m-d");
		$data = [];
		$expiration = strtotime(date("Y-m-d") . ' ' . $redcancel);
		$d = ['addtime' => $now, 'expiration'=> $expiration];
		for ($i=1; $i <= $num; $i++) {
			$d['redcode'] = 'R' . $now . str_pad($i,$length,'0',STR_PAD_LEFT);
			$data[] = $d;
		}

		$table = 'red';
		$r = Db::name($table)->insertAll($data);//插入所有数据
		if ($r) {
			$cachename = 'RED_' .$date;
			Cache::set($cachename,$r,86400);
		}
		return $r;
	}

	/**
	 * 创建带有兑换码的优惠券
	 * 小程序端有用兑换码兑换优惠券的功能
	 * 基于策略：先生成一个兑换码的池子，并保证每次生成优惠券（一对一关联兑换码）时需生成的数量 <= 池子数量的 1/10，优惠券关联兑换码时保证随机性
	 * @param  int $count [数量]
	 * @param  int $expiration [有效期]
	 * @return [type]        [description]
	 */
	public function createRedOfSpecial()
	{
		$count = isset($_REQUEST['count']) ? intval(trim($_REQUEST['count'])) : 0;
		$expiration = isset($_REQUEST['expiration']) ? trim($_REQUEST['expiration']) : 0;

		if (empty($count) || empty($expiration)) {
			return array('code' => 100, 'msg'=>'param error');
		}elseif ($count < 1 || $count > 200) {
			return array('code' => 100, 'msg'=>'数量填写错误');
		}elseif(strtotime($expiration) < time() ){
			return array('code' => 100, 'msg'=>'过期时间请大于此刻');
		}

		# 池子可用兑换码数量
		$total = Db::query("select count(1) as total from `redeemcode` where `status` = 0");
		$total = $total[0]['total'];
		if ($total < $count * 10) {//不足时补充兑换码池子
			$create_number = $count * 10 - $total;
			$r = $this->createRedeemCode($create_number);
			if (!$r) {
				return array('code' => 100, 'msg'=>'生成兑换码失败');
			}
		}
		//获取可用兑换码(用于关联优惠券)
		$codelist = $this->getAvailableCode($count);
		if (!$codelist || count($codelist) != $count) {
			return array('code' => 100, 'msg'=>'获取兑换码失败');
		}
		$codeids = array_column($codelist, 'id');


		$length = strlen($count);
		$now 	= time();
		$data 	= [];
		$d 		= ['type' => 2, 'addtime' => $now, 'expiration' => strtotime($expiration)];
		for ($i=1; $i <= $count; $i++) {
			$d['redcode'] = 'R' . $now . str_pad($i,$length,'0',STR_PAD_LEFT);
			$code = array_shift($codelist);
			$d['takecode'] = $code['code'];
			$data[] = $d;
		}

		//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "";
		try{
			$r = Db::name('red')->insertAll($data);//插入所有数据

			$sql = "update `redeemcode` set `status` = 1 where id in ('" . implode("','", $codeids) . "')";
			$affect_count = Db::execute($sql);//设置状态为已使用
			// var_dump($affect_count);
			if($affect_count != $count){
				$rollback = true;
			}
		} catch (\Exception $e) {
			// var_dump($e->getMessage());
		   $rollback = true;
		}
		if ($rollback !== false) {
		    Db::rollback();
		    $msg = $msg ? $msg : 'database error';
		    return ['code'=>106,'msg'=>$msg];
		}
	    Db::commit();	 
		return array('code' => 200, 'msg'=>'成功生成'. $count . '张优惠券，请注意过期时间');
	}

	/**
	 * 获取可用的兑换码（status = 0）
	 * @param  [type] $count [description]
	 * @return [type]        [description]
	 */
	public function getAvailableCode($count)
	{
		if ($count < 0) {
			return false;
		}
		# 池子可用兑换码数量
		$total = Db::query("select count(1) as total from `redeemcode` where `status` = 0");
		$total = $total[0]['total'];

		$codelist = [];
		$codeids = [];
		while(count($codelist) < $count){//此处有死循环的风险，必须保证兑换码池子内数量足够
			$rand = rand(1, $total);
			$code = Db::query("select id,code  from `redeemcode` where `status` = 0 limit {$rand},1 ");
			if (!$code) {
				return false;
			}
			$code = $code[0];
			if (in_array($code['id'], $codeids)) {
				continue;
			}
			$codelist[] = $code;
			$codeids[] = $code['id'];
		}
		if (count($codelist) == $count) {
			return $codelist;
		}
		return false;
	}

	/**
	 * 生成一定数量的兑换码
	 * 思路是把数据库id的十进制值转换为对应的字符串值（某种进制）
	 * @param  [type] $count [description]
	 * @return [type]        [description]
	 */
	public function createRedeemCode($count)
	{
		$count = intval($count);
		if ($count > 2000 || $count < 0) {
			return false;
		}

		$table = 'redeemcode';
		$d = Db::query("select max(`id`) as maxid from `{$table}` where 1");
		$index = 1;//起始值
		if ($d && $d[0]['maxid']) {
			$index = $d[0]['maxid'];
		}

		$data = [];
		for ($i=1; $i <= $count; $i++) { 
			$id = $index + $i;
			$code = $this->numberToCode($id);
			$data[] = ['id' => $id, 'code' => $code ];
		}
		$r = Db::name($table)->insertAll($data);//插入所有数据
		return $r;
	}

	/**
	 * 数字转换为字符串（8位） 
	 * 最大为34^8，不能超过这个值
	 * @param  [type] $number [description]
	 * @return [type]         [description]
	 */
	public function numberToCode($number)
	{
		//去掉难以判别的I和O
		//以下字符分别对应十进制0-33则此转换为34进制
		$characters = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$A = [];//存储多进制的数字值
		$b = 34;//除数 这里表示34进制
		$R = intval($number);
		while ($R != 0) {
			$yushu = $R % $b;
			array_unshift($A, $yushu);//余数填充到数组头部(拼接则为N进制数)
			$R = intval($R / $b);//商作为下一次循环的被除数
		}

		$code = '';
		foreach ($A as $v) {
			$code .= $characters[$v];
		}
		$code = str_pad($code,8,'0',STR_PAD_LEFT);//左边用0补全八位
        return $code;
	}


	/**
	 * 生成指定数目的优惠券
	 * @param  int
	 * @return [type]
	 */
	public function test(){
		$cachename = 'RED_123';
		Cache::set($cachename,'1233',86400);
		return 1;
	}

	/**
	 * 优惠券列表
	 */
	public function redlist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		$where = [];
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "userid <= '{$userid}'";
		}
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}

		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'red';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = self::$statusArr;
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['status'] 	= $statusArr[$v['status']];
			$v['gettime'] 	= empty($v['gettime'])?'':date('Y-m-d H:i:s',$v['gettime']);
			$v['expiration'] 	= empty($v['expiration'])?'':date('Y-m-d H:i:s',$v['expiration']);
			$v['type'] 	= $v['type'] == 1?'普通':'手动生成';
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 计算当前优惠券数目并更新缓存
	 */
	public function calred(){
		$date 	= date("Y-m-d");
		$now 	= time();

		$cachename = 'RED_' .$date;
		$table 	= 'red';
		$sql 	= "select count(1) as total from `{$table}` where  expiration > {$now} and status = 1 and type = 1 ";
		$r 		= DB::query($sql);
		if (!empty($r[0])) {
			$num = $r[0]['total'];
		}else{
			$num = 0;
		}
		//设置缓存
		Cache::set($cachename,$num,86400);
		return $num;
	}

	/**
	 * 未使用的(过期优惠券)优惠券作废
	 * 条件过期 && （未领取 或 已领取状态 ）
	 */
	public function enablered(){
		$now = time();
		$sql = "update `red` set `status` = 4 where `expiration` < '{$now}' and `status` <= 2";
		$num = Db::execute($sql);

		$this->calred();
		return $num;
	}
}

