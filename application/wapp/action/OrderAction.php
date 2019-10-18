<?php

namespace app\wapp\action;

use think\Db;

class OrderAction{

	static $statusArr = ['1'=>'已发布','2'=>'已接单','3'=>'已完成','4'=>'已取消'];

	/**
	 * 发单接口
	 * 
	 */
	public function orderadd(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$address_s 	= isset($_REQUEST['address_s']) ? trim($_REQUEST['address_s']) : '';//取件点
		$address_e 	= isset($_REQUEST['address_e']) ? trim($_REQUEST['address_e']) : '';//收货点
		$msg 		= isset($_REQUEST['msg']) ? trim($_REQUEST['msg']) : '';//短信消息
		$code 		= isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';//取件码
		$paypoint 	= isset($_REQUEST['paypoint']) ? intval(trim($_REQUEST['paypoint'])) : '';//使用积分
		$isred 		= isset($_REQUEST['isred']) ? intval(trim($_REQUEST['isred'])) : '';//是否使用优惠券
		$redcode 	= isset($_REQUEST['redcode']) ? trim($_REQUEST['redcode']) : '';//优惠券码
		$note 		= isset($_REQUEST['note']) ? trim($_REQUEST['note']) : '';//备注

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($address_s)){
			return ['code'=>100,'msg'=>'address_s empty'];
		}elseif (strlen($address_s) > 255) {
			return ['code'=>100,'msg'=>'address_s greater than 255'];
		}
		if(empty($address_e)){
			return ['code'=>100,'msg'=>'address_e empty'];
		}elseif (strlen($address_e) > 255) {
			return ['code'=>100,'msg'=>'address_e greater than 255'];
		}
		if(empty($msg)){
			// return ['code'=>100,'msg'=>'msg empty'];
		}elseif (strlen($msg) > 255) {
			return ['code'=>100,'msg'=>'短信消息不能超过255字哦'];
		}
		if(empty($code)){
			return ['code'=>100,'msg'=>'code empty'];
		}elseif (strlen($code) > 20) {
			return ['code'=>100,'msg'=>'code greater than 20'];
		}
		if(empty($paypoint)){
			return ['code'=>100,'msg'=>'paypoint empty'];
		}elseif (false) {//判断金额
			# code...
		}
		if(empty($isred)){
			return ['code'=>100,'msg'=>'isred empty'];
		}
		if(!empty($note) && strlen($note) > 255){
			return ['code'=>100,'msg'=>'备注不能超过255字哦'];
		}

/*		if($isred == 2 && empty($redcode)){
			return ['code'=>100,'msg'=>'redcode empty'];
		}*/
		$now = time();

		//基础配置
		$SettingAction = new \app\index\action\SettingAction();
		$conf = $SettingAction->getbaseconf();
		if (empty($conf['closing_time']) || $now > strtotime($conf['closing_time'])) {
			return ['code'=>200,'msg'=>'不要意思,本小店已打烊','result_code'=>"FAIL"];
		}

		if($isred == 2 ){
			$redaction = new \app\wapp\action\RedAction();
			$hasred = $redaction->hasred($userid);
			if (empty($hasred['redcode'])) {
				return ['code'=>200,'msg'=>'您没有优惠券','result_code'=>"FAIL"];
			}
			$redcode = $hasred['redcode'];

		}

		
		$table = 'order';
		$data = [];
		$data['userid'] 	= $userid;
		$data['address_s'] 	= $address_s;
		$data['address_e'] 	= $address_e;
		$data['msg']		= $msg;
		$data['code']		= $code;
		$data['paypoint'] 	= $paypoint;
		$data['isred']		= $isred;
		$data['relpoint'] 	= $isred == 2?($paypoint - 20) : $paypoint;
		$data['redcode'] 	= $redcode;
		$data['note']		= $note;
		$data['addtime'] 	= $now;

		$c = explode('$$$', $address_e);
		if (!empty($c)) {
			$data['addr_one'] = $c[0];
			$data['addr_two'] = $c[1];
			$data['addr_three'] = $c[2];
			$data['room'] = $c[3];
			$data['name'] = $c[4];
			$data['phone'] = $c[5];
			$data['address_e'] = implode(" ", $c);
		}

		//判断金额是否足够
		$memberModel 	= new \app\index\model\MemberModel();
		$userone 		= $memberModel->get($userid);
		if (!$userone) {
			return ['code'=>100,'msg'=>'no user data'];
		}elseif (intval($userone['point']) < $data['relpoint']) {
			return ['code'=>200,'msg'=>'余额不足','result_code'=>"FAIL"];
		}elseif ($userone['status'] == 2) {//黑名单
			return ['code'=>200,'msg'=>'不好意思,系统拒绝了您的下单请求,具体信息请联系客服','result_code'=>"FAIL"];
		}

		//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "";
		try{
			$r = $r2 = $r3 = true;
			//更新订单信息
			$r 			= Db::table($table)->insert($data);
			$orderid 	= Db::name($table)->getLastInsID();
			//更新用户金额
			if (!empty($data['relpoint'])) {
				$pointnew 	= $userone['point'] - $data['relpoint'];
				$r2			= Db::name('member')->where('id', $userid)->update(['point' => $pointnew]);
			}
			//更新优惠券
			if ($isred == 2) {
				$r3			= Db::name('red')->where('redcode', $redcode)->update(['status' => 3]);
			}
			if (!($r && $r2 && $r3)) {
				$rollback = true;
			}
		} catch (\Exception $e) {
		   $rollback = true;
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>'update database error'];
		}
	    Db::commit();	 
		return ['code'=>200,'msg'=>'下单成功','result_code'=>"FAIL"];
	}

	/**
	 * 提现单
	 */
	public function withdraw(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$paypoint 	= isset($_REQUEST['paypoint']) ? intval(trim($_REQUEST['paypoint'])) : '';//提现积分金额
		$openid 	= isset($_REQUEST['openid']) ? intval(trim($_REQUEST['openid'])) : '';//openid
		$wxaccount 	= isset($_REQUEST['wxaccount']) ? trim($_REQUEST['wxaccount']) : '';//微信号
		
		//校验数据
		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($paypoint)){
			return ['code'=>100,'msg'=>'paypoint empty'];
		}elseif ($paypoint < 0 || $paypoint % 10 != 0) {//必须为10的整数
			return ['code'=>100,'msg'=>'paypoint error'];
		}
		if(empty($wxaccount)){
			return ['code'=>100,'msg'=>'wxaccount empty'];
		}

		//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "update database error";
		try{
			$now 	= time();
			$pay 	= $paypoint/10;
			$table 	= 'withdrawrecord';
			$user  	= db('member')->where("id = '{$userid}'")->select();
			if(empty($user)){
				throw new \Exception("get user error", 1);
			}
			$user 		= $user[0];
			$haspoint 	= $user['point'];
			$openid 	= $user['openid'];
			if($haspoint < $paypoint){
				throw new \Exception("no enough point", 1);
			}
			$newpoint = $haspoint - $paypoint;

			//插入提现记录
			$data 	= array('userid' => $userid, 'openid'=>$openid, 'paypoint'=>$paypoint, 'pay'=>$pay, 'addtime'=>$now, 'wxaccount'=> $wxaccount);
			$r 		= Db::table($table)->insert($data);

			//更新用户积分
			$u 	= ['point' => $newpoint];
			$r2 = Db::table('member')->where("id = '{$userid}'")->update($u);
		}catch(\Exception $e){
			$rollback = true;
			$msg = $e->getMessage();
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>$msg];
		}
	    Db::commit();	 
		return ['code'=>200,'msg'=>'提现成功，请耐心等待1-3个工作日','result_code'=>"SUCCESS"];
	}

	/**
	 * 取消订单接口
	 * 
	 */
	public function orderfinish(){
		$userid 		= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$orderid 		= isset($_REQUEST['orderid']) ? trim($_REQUEST['orderid']) : '';//订单id

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($orderid)){
			return ['code'=>100,'msg'=>'orderid empty'];
		}

		$table = 'order';
		$orderone = db($table)->where("id = '{$orderid}' and userid = '{$userid}'")->select();
		if (empty($orderone[0])) {
			return ['code'=>100,'msg'=>'order not found'];
		}

		$now = time();
		$sql 	= "update `order` set `status` = 3, `finishtime` = '{$now}'  where  `id` = '{$orderid}'";
		// echo $sql;
		$r 		= Db::execute($sql);
		return ['code'=>200,'msg'=>'订单完成','result_code'=>"SUCCESS"];
	}


	/**
	 * 取消订单接口
	 * 
	 */
	public function ordercancel(){
		$userid 		= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$orderid 		= isset($_REQUEST['orderid']) ? trim($_REQUEST['orderid']) : '';//订单id

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($orderid)){
			return ['code'=>100,'msg'=>'orderid empty'];
		}

		$table = 'order';
		$orderone = db($table)->where("id = '{$orderid}' and userid = '{$userid}'")->select();
		if (empty($orderone[0])) {
			return ['code'=>100,'msg'=>'order not found'];
		}
		$orderone = $orderone[0];//echo json_encode($orderone);
		if ($orderone['status'] != 1) {
			$msg = "订单状态为" . self::$statusArr[$orderone['status']] .",不能取消"; 
			return ['code'=>101,'msg'=>$msg];
		}

		//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "";
		try{
			$relpoint 	= $orderone['relpoint'];
			$isred		= $orderone['isred'];
			$now = time();
			$r = $r2 = $r3 = true;
			//更新订单信息
			$r 			= Db::table($table)->where('id', $orderid)->update(['status' => 4]);//已取消
			//更新用户金额
			if (!empty($relpoint)) {
				// $memberModel 	= new \app\index\model\MemberModel();
				// $userone 		= $memberModel->get($userid);
				// $pointnew 	= $userone['point'] + $relpoint;
				$r2			= Db::table('member')->where('id', $userid)->setInc('point', $relpoint);//tp自增方法
			}
			//更新优惠券
			if ($isred == 2) {
				$redcode 	= $orderone['redcode'];
				// $r3			= Db::table('red')->where('redcode', $redcode)->update(['status' => 2]);//恢复优惠券状态为已领取 modify 取消订单不返还优惠券
				
			}
			if (!($r && $r2 && $r3)) {
				$rollback = true;
			}
		} catch (\Exception $e) {
		   $rollback = true;
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>'update database error'];
		}
	    Db::commit();	 
		return ['code'=>200,'msg'=>'取消订单成功','result_code'=>"SUCCESS"];
	}

	/**
	 * 用户获取订单
	 */
	public function userOrderlist(){
		$page 		= isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit 		= isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid 	= isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$status 	= isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

		//检测必填参数
		if (empty($userid)) {
			return ['code'=>100,'msg'=>'userid empty'];
		}
		

		$where = [];
		$where[] = "userid = '{$userid}'";
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'order';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = self::$statusArr;
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['status'] 	= $statusArr[$v['status']];
			$v['isred'] 	= $v['isred'] == 1?"否":"是";
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 插入一条充值记录
	 * 成功返回记录id
	 */
	public function addDepositRecord($data){
		if (empty($data)) {
			return false;
		}
		$table = 'depositrecord';
		$r = Db::table($table)->insert($data);
		if (!$r) {
			return false;
		}
		$id = Db::table($table)->getLastInsID();
		return $id;
	}


}

