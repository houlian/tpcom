<?php

namespace app\wapp\action;

use think\Db;
use think\Cache;

class RedAction{

	/**
	 * 用户领取优惠券
	 */
	public function sendred(){
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		if (empty($userid)) {
			return ['code'=>100,'msg'=>'userid empty'];
		}
		$table = 'red';
		$date = date("Y-m-d");
		$cachename = 'RED_' .$date;
		$num = Cache::get($cachename);
		if ($num === false) {//缓存不存在重新设置缓存
			$num = (new \app\index\action\RedAction())->calred();
		}
		if ($num <= 0) {
			return ['code'=>200,'msg'=>'优惠券被抢光了,下次早点来','type'=>2,'redcode'=>''];
		}

		//是否在领取时间段
		$now = time();
		$redconf = $this->redconf();
		if (($now > strtotime($redconf['am_s']) && $now < strtotime($redconf['am_e'])) || ($now > strtotime($redconf['pm_s']) && $now < strtotime($redconf['pm_e']))) {
				//在领取时间段内
		}else{
			return ['code'=>200,'msg'=>'不在领取时间段','type'=>2,'redcode'=>''];
		}

		
		$start = strtotime(date('Y-m-d'));
		$end = $start + 86400;

		//暂时不考虑高并发下的问题//高并发下可以用数据链表（缓存）实现

		//判断是否该用户已领取
		$sql 	= "select id from `{$table}` where  addtime > {$start} and addtime < $end and userid = '{$userid}' and type = 1 limit 1";
		$r 		= DB::query($sql);
		if (!empty($r)) {
			return ['code'=>200,'msg'=>'每人限领一个','type'=>3,'redcode'=>''];
		}

		//获取码
		$sql 	= "select id, redcode from `{$table}` where  addtime > {$start} and addtime < $end and status = 1 and type = 1 order by id limit 1";
		$r 		= DB::query($sql);

		if (empty($r[0])) {
			return ['code'=>200,'msg'=>'优惠券被抢光了,下次早点来','type'=>2,'redcode'=>''];
		}

		//缓存自减//修改状态标识		
		$id 		= $r[0]['id'];
		$redcode 	= $r[0]['redcode'];
		$update 	= " update `{$table}` set `status` = 2 , userid = '{$userid}' , gettime = '{$now}' where id = '{$id}' and `status` = 1";
		// echo $update . '<br>';
		$rr 		= DB::execute($update);
		if (!$rr) {
			return ['code'=>103,'msg'=>'network error please try aggin','type'=>2,'redcode'=>''];
		}
        try {
			Cache::dec($cachename);
        } catch (Exception $e) {
            // Cache::set($cachename, $num - 1,86400);
        }
		
		return ['code'=>200,'msg'=>'领取成功','type'=>1,'redcode'=>$redcode,'count'=> ($num - 1)];
	}

	/**
	 * 是否有优惠券
	 * @param  [type]
	 * @return [type]
	 */
	public function hasred($userid){
		$table = 'red';
		$now 	= time();
		$sql 	= "select redcode,status from `{$table}` where userid = '{$userid}' and expiration > '{$now}' and `status` = 2  order by expiration asc limit 1";
		$r 		= DB::query($sql);
		if (!empty($r)) {
			return ['hasred'=>2, 'redcode'=>$r[0]['redcode'], 'usered'=>$r[0]['status']];
		}
		return ['hasred'=>1, 'redcode'=>'', 'usered'=>''];
	}
	
	/**
	 * 获取当前优惠券数量
	 * @param bool $[iscal] [是否重新计算]
	 */
	public function getrednumber($iscal = false){
		$date 	= date("Y-m-d");

		$cachename = 'RED_' .$date;
		$num = Cache::get($cachename);
		if ($num === false || $iscal === true) {
			//重新计算剩余优惠券数目
			$num = (new \app\index\action\RedAction())->calred();
		}
		if ($num < 0) {
			$num = 0;
		}

		$now = time();
		$rednotice = 1;
		if ($num > 0) {
			$redconf = $this->redconf();
			//提前五分钟提醒
			if (($now > (strtotime($redconf['am_s']) - 300) && $now < strtotime($redconf['am_e'])) || ($now > (strtotime($redconf['pm_s'])-300) && $now < strtotime($redconf['pm_e']))) {
				//在提醒时间段内
				$rednotice = 2;
			}		
		}
		return ['code'=>200,'msg'=>'success','rednum' => $num, "rednotice"=> $rednotice];
	}

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
			$conf = ['num'=>100, 'am_s'=>'9:30:00','am_e'=>'10:00:00','pm_s'=>'15:00:00','pm_e'=>'16:00:00','redcancel'=>'17:00:00'];
		}
		return $conf;
	}


	/**
	 * 通过兑换码获取优惠券
	 * @param string $value [description]
	 */
	public function codetored()
	{
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$code 	= isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		if (empty($userid)) {
			return ['code'=>100,'msg'=>'userid empty'];
		}elseif(empty($code)){
			return ['code'=>100,'msg'=>'code empty'];
		}
		
		$d = Db::query("select * from `red` where takecode=?",[$code]);
		if (empty($d[0])) {
			return ['code'=>100,'msg'=>'兑换码错误'];
		}

		$now = time();
		$red = $d[0];
		if ($red['expiration'] < $now) {
			return ['code'=>100,'msg'=>'兑换码已过期'];
		}elseif($red['status'] != 1){
			if ($red['status'] == 2 || $red['status'] == 2 ) {
				return ['code'=>100,'msg'=>'该兑换码已被使用'];
			}else{
				return ['code'=>100,'msg'=>'兑换码过期了'];
			}
		}

		$r = db('red')->where("id", $red['id'])->update(['status'=>2, 'userid' => $userid, 'gettime' => $now]);
		return ['code'=>200,'msg'=>'恭喜您！成功领取一张优惠券'];

	}

	/**
	 * 用户优惠券分页数据
	 */
	public function userRedList(){
		$page 		= isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit 		= isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid 	= isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';

		//检测必填参数
		if (empty($userid)) {
			return ['code'=>100,'msg'=>'userid empty'];
		}
		

		$where = [];
		$where[] = "userid = '{$userid}'";
		
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'red';
		$data 	= db($table)->where($where)->page($page, $limit)->order(['status' => 'asc','id'=>'desc'])->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = [1 => '未领取', 2 => '未使用', 3 => '已使用', 4 => '已过期'];
		foreach ($data as &$v) {
			$v['gettime'] 	= empty($v['gettime'])?'':date('Y-m-d H:i:s',$v['gettime']);
			$v['expiration'] = empty($v['expiration'])?'':date('Y-m-d H:i:s',$v['expiration']);
			$v['status_text'] 	= $statusArr[$v['status']];
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data, 'limit' => $limit, 'page' => $page);//200代表正常
		return $r;
	}
}

