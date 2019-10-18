<?php

namespace app\wapp\action;

use think\Model;
use think\Db;

class MemberAction{

	/**
	 * 登陆插入用户数据并返回基础数据
	 * @param  [string]
	 * @param  [string]
	 * @return [array]
	 */
	public function wappLogin($openid, $session_key){
		if (empty($openid) || empty($session_key)) {
			return false;
		}

		$table = "member";
		$now = time();
		$user = [];
		$isexist = Db::query("select * from `{$table}` where openid= '{$openid}'");
		if (!$isexist) {
			$data = array('openid' => $openid, 'regtime'=>$now, 'sessionkey'=>$session_key);
			Db::table($table)->insert($data);
			$userId = Db::name($table)->getLastInsID();
			$user['id'] = $userId;
			$user['status'] = 1;
			$user['mobile'] = '';
			$user['point'] = 0;
			$user['addrlist'] = '';
			$user['openid']	= $openid;
		}else{
			# 更新session_key
			$userid = $isexist[0]['id'];
			$r = Db::table($table)->where("id = '{$userid}'")->update(['sessionkey' => $session_key]);//更新会话秘钥

			$isexist 		= $isexist[0];
			$user['id'] 	= $isexist['id'];
			$user['status'] = $isexist['status'];
			$user['mobile'] = $isexist['mobile'];
			$user['point'] 	= $isexist['point'];
			$user['openid'] = $openid;
			//获取添加的地址列表
			$user['addrlist'] = '';
		}

		return $user;
	}


	/**
	 * 用户添加地址处理
	 */
	public function addressadd(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$address_one 	= isset($_REQUEST['address_one']) ? trim($_REQUEST['address_one']) : '';//一级地址
		$address_two 	= isset($_REQUEST['address_two']) ? trim($_REQUEST['address_two']) : '';//二级地址
		$address_three 	= isset($_REQUEST['address_three']) ? trim($_REQUEST['address_three']) : '';//三级地址
		$room 			= isset($_REQUEST['room']) ? trim($_REQUEST['room']) : '';//昵称
		$name 			= isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';//昵称
		$phone 			= isset($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';//收货点

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($address_one)){
			return ['code'=>100,'msg'=>'address_one empty'];
		}elseif (strlen($address_one) > 20) {
			return ['code'=>100,'msg'=>'address_one greater than 20'];
		}
		if(empty($address_two)){
			return ['code'=>100,'msg'=>'address_two empty'];
		}elseif (strlen($address_two) > 20) {
			return ['code'=>100,'msg'=>'address_two greater than 20'];
		}
		if(empty($address_three)){
			return ['code'=>100,'msg'=>'address_three empty'];
		}elseif (strlen($address_three) > 20) {
			return ['code'=>100,'msg'=>'address_three greater than 20'];
		}
		if(empty($room)){
			return ['code'=>100,'msg'=>'room empty'];
		}elseif (strlen($room) > 16) {
			return ['code'=>100,'msg'=>'room greater than 16'];
		}
		if(empty($name)){
			return ['code'=>100,'msg'=>'name empty'];
		}elseif (strlen($name) > 16) {
			return ['code'=>100,'msg'=>'name greater than 16'];
		}
		if(empty($phone)){
			return ['code'=>100,'msg'=>'phone empty'];
		}elseif (strlen($phone) > 20) {
			return ['code'=>100,'msg'=>'phone greater than 20'];
		}

		$table = 'useraddress';
		$data = array('userid' => $userid, 'addressone'=>$address_one, 'addresstwo'=>$address_two, 'addressthree'=>$address_three, 'name'=>$name, 'phone'=>$phone,'room'=> $room);
		$r = Db::table($table)->insert($data);
		// $insertid = Db::name($table)->getLastInsID();
		if (!$r) {
			return ['code'=>102,'msg'=>'add database error'];
		}
		return ['code'=>200,'msg'=>'success'];
	}

	/**
	 * 用户修改地址处理
	 */
	public function addressmodify(){
		$userid 		= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$addressid 		= isset($_REQUEST['addressid']) ? trim($_REQUEST['addressid']) : '';//id
		$address_one 	= isset($_REQUEST['address_one']) ? trim($_REQUEST['address_one']) : '';//一级地址
		$address_two 	= isset($_REQUEST['address_two']) ? trim($_REQUEST['address_two']) : '';//二级地址
		$address_three 	= isset($_REQUEST['address_three']) ? trim($_REQUEST['address_three']) : '';//三级地址
		$room 			= isset($_REQUEST['room']) ? trim($_REQUEST['room']) : '';//昵称
		$name 			= isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';//昵称
		$phone 			= isset($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';//收货点

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($addressid)){
			return ['code'=>100,'msg'=>'addressid empty'];
		}
		if(empty($address_one)){
			return ['code'=>100,'msg'=>'address_one empty'];
		}elseif (strlen($address_one) > 20) {
			return ['code'=>100,'msg'=>'address_one greater than 20'];
		}
		if(empty($address_two)){
			return ['code'=>100,'msg'=>'address_two empty'];
		}elseif (strlen($address_two) > 20) {
			return ['code'=>100,'msg'=>'address_two greater than 20'];
		}
		if(empty($address_three)){
			return ['code'=>100,'msg'=>'address_three empty'];
		}elseif (strlen($address_three) > 20) {
			return ['code'=>100,'msg'=>'address_three greater than 20'];
		}
		if(empty($room)){
			return ['code'=>100,'msg'=>'room empty'];
		}elseif (strlen($room) > 16) {
			return ['code'=>100,'msg'=>'room greater than 16'];
		}
		if(empty($name)){
			return ['code'=>100,'msg'=>'name empty'];
		}elseif (strlen($name) > 16) {
			return ['code'=>100,'msg'=>'name greater than 16'];
		}
		if(empty($phone)){
			return ['code'=>100,'msg'=>'phone empty'];
		}elseif (strlen($phone) > 20) {
			return ['code'=>100,'msg'=>'phone greater than 20'];
		}

		$table = 'useraddress';
		$data = array('addressone'=>$address_one, 'addresstwo'=>$address_two, 'addressthree'=>$address_three, 'name'=>$name, 'phone'=>$phone,'room'=>$room);
		$r = Db::table($table)->where("id = '{$addressid}'")->update($data);
		return ['code'=>200,'msg'=>'success'];
	}

	/**
	 * 用户添加地址处理
	 */
	public function addressdelete(){
		$userid 		= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$addressid 		= isset($_REQUEST['addressid']) ? trim($_REQUEST['addressid']) : '';//id

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($addressid)){
			return ['code'=>100,'msg'=>'addressid empty'];
		}

		$table = 'useraddress';
		$data = array('status'=>2);
		$r = Db::table($table)->where("id = '{$addressid}'")->update($data);
/*		if (!$r) {
			return ['code'=>102,'msg'=>'update database error'];
		}*/
		return ['code'=>200,'msg'=>'success'];
	}


	/**
	 * 获取用户地址列表
	 */
	public function addresslist(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}

		$table = 'useraddress';
		$r = db($table)->where("userid = '{$userid}' and status = 1")->select();
		if (!$r) {
			return ['code'=>200,'msg'=>'data nothing','data'=>''];
		}
		return ['code'=>200,'msg'=>'success','data'=>$r];
	}

	/**
	 * 用户充值 积分增加
	 * @param $count 积分
	 */
	public function pointplus($out_trade_no){
		if (empty($out_trade_no)) {
			return false;
		}
		$sql = "select userid,pay from depositrecord where id = '{$out_trade_no}' limit 1";
		// echo $sql . "<br>";
		$data = Db::query($sql);
		if(empty($data[0])){
			return false;
		}

		$userid 	= $data[0]['userid'];
		$pay  		= $data[0]['pay'];
		$pointplus 	= intval($pay / 10);//分转为积分
		// $pointplus 	= $pay;//
		if ($pointplus < 1) {
			return true;
		}
		$sql 		= "update `member` set `point` = `point` + {$pointplus} where id = '{$userid}'";
		// echo $sql . "<br>";
		$r 			= Db::execute($sql);
		if ($r) {
			return true;
		}
		return false;
	}

	/**
	 * 获取用户基本信息
	 * @return [type]
	 */
	public function memberinfo(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}

		$table = 'member';
		$r = db($table)->where("id = '{$userid}'")->select();
		if (empty($r[0])) {
			return ['code'=>200,'msg'=>'data nothing','data'=>''];
		}
		$user = $r[0];

       //是否有优惠券
        $hasred 	= (new \app\wapp\action\RedAction())->hasred($user['id']);
        $user 		= array_merge($user, $hasred);

        $regtime 	= date('Ym', $user['regtime']);
        $zddid 		= $regtime . $user['id'];
        $user['zddid'] = $zddid;
		return ['code'=>200,'msg'=>'success','data'=>$user];
	}


	/**
	 * 用户提出意见
	 */
	public function opinion(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$opinion 	= isset($_REQUEST['opinion']) ? trim($_REQUEST['opinion']) : '';//一级地址

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($opinion)){
			return ['code'=>100,'msg'=>'opinion empty'];
		}elseif (strlen($opinion) > 140) {
			return ['code'=>200,'result_code'=>'FAIL','msg'=>'opinion greater than 140'];
		}

		$table 	= 'opinion';
		$now 	= time();
		$data 	= array('userid' => $userid, 'opinion'=>$opinion,'addtime'=>$now);
		$r = Db::table($table)->insert($data);
		// $insertid = Db::name($table)->getLastInsID();
		if (!$r) {
			return ['code'=>102,'msg'=>'add database error'];
		}
		return ['code'=>200,'result_code'=>'SUCCESS','msg'=>'success'];
	}

	/**
	 * 用户更新基本信息
	 */
	public function updatemember(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		$nickname 	= isset($_REQUEST['nickname']) ? trim($_REQUEST['nickname']) : '';//一级地址

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($nickname)){
			return ['code'=>100,'msg'=>'nickname empty'];
		}elseif (strlen($nickname) > 140) {
			return ['code'=>200,'result_code'=>'FAIL','msg'=>'nickname greater than 140'];
		}

		$table 	= 'member';
		$data 	= array('name'=>$nickname);
		$r = Db::table($table)->where("id = '{$userid}'")->update($data);//有可能名称不变
		// $insertid = Db::name($table)->getLastInsID();
		if (!$r) {
			// return ['code'=>102,'msg'=>'update database error'];
		}
		return ['code'=>200,'result_code'=>'SUCCESS','msg'=>'success'];
	}

	/**
	 * 更新用户手机号
	 * @return [type] [description]
	 */
	public function updatephone(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';//用户id
		// $phone 	= isset($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';//
		$encryptedData 	= isset($_REQUEST['encryptedData']) ? trim($_REQUEST['encryptedData']) : '';//
		$iv 	= isset($_REQUEST['iv']) ? trim($_REQUEST['iv']) : '';//
		$table 	= 'member';

		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}elseif (empty($encryptedData) || empty($iv)) {
			return ['code'=>101,'msg'=>'param error'];
		}elseif (strlen($iv) != 24) {
			return ['code'=>101,'msg'=>'Illegal Iv'];
		}
		$r = db($table)->where("id = '{$userid}'")->select();
		if (empty($r[0]['sessionkey'])) {
			return ['code'=>100,'msg'=>'sessionkey nothing'];
		}
		$sessionKey = $r[0]['sessionkey'];


		$WappAction = new \app\wapp\action\WappAction();
		$appid = $WappAction->getAppid();

		$phone = $this->decryptData($encryptedData, $iv, $sessionKey, $appid);
		if (!$phone) {
			return ['code'=>100,'msg'=>'phone decry error'];
		}
		
		$data 	= array('mobile'=> $phone);
		$r 		= Db::table($table)->where("id = '{$userid}'")->update($data);//有可能名称不变
		return ['code'=>200,'result_code'=>'SUCCESS','msg'=>'success'];
	}

	private function decryptData($encryptedData, $iv, $sessionKey, $appid)
	{
		$aesKey=base64_decode($sessionKey);

		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return false;
		}
		if( $dataObj->watermark->appid != $appid )
		{
			return false;
		}
		return $dataObj->purePhoneNumber;
	}		
}

