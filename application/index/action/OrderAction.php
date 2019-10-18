<?php

namespace app\index\action;

use think\Model;
use think\Db;
use think\Log;

class OrderAction{

	static $statusArr = ['1'=>'已发布','2'=>'已接单','3'=>'已完成','4'=>'已取消'];//订单状态
	static $desportStatus = ['0'=>'选择状态','1'=>'生成中','2'=>'支付成功' , '3'=>'支付未完成'];//充值记录
	static $withdrawStatus = ['0'=>'选择状态','1'=>'未支付','2'=>'完成提现' , '3'=>'提现取消'];//充值记录

	/**
	 * 订单列表
	 */
	public function orderlist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		$phone = isset($_REQUEST['phone']) ? intval(trim($_REQUEST['phone'])) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
		$isred = isset($_REQUEST['isred']) ? trim($_REQUEST['isred']) : '';
		$paypoint = isset($_REQUEST['paypoint']) ? trim($_REQUEST['paypoint']) : '';
		$name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';


		$where = [];
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($code)) {
			$where[] = "code = '{$code}'";
		}
		if (!empty($name)) {
			$where[] = "name = '{$name}'";
		}
		if (!empty($phone)) {
			$where[] = "phone = '{$phone}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "addtime <= '{$timeend}'";
		}
		if (!empty($isred)) {
			$where[] = "isred = '{$isred}'";
		}
		if (!empty($paypoint)) {
			$where[] = "paypoint = '{$paypoint}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		// echo $where;
		$table 	= 'order';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = self::$statusArr;
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['finishtime'] 	= empty($v['finishtime'])?'':date('Y-m-d H:i:s',$v['finishtime']);
			$v['statustext'] 	= $statusArr[$v['status']];
			$v['isred'] 	= $v['isred'] == 1?"否":"是";
			// $v['address_e'] 	= $v['addr_one'] . " | " . $v['addr_two'] . " | " . $v['addr_three'] . " | " . $v['room'] . " | " . $v['name'] . " | " . $v['phone'];
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 充值列表
	 */
	public function depositlist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$wxrecord = isset($_REQUEST['wxrecord']) ? trim($_REQUEST['wxrecord']) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';


		$where = [];
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		if (!empty($wxrecord)) {
			$where[] = "transaction_id = '{$wxrecord}'";
		}
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "addtime <= '{$timeend}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'depositrecord';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = self::$desportStatus;
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['status'] 	= $statusArr[$v['status']];
			$v['payyun'] 	= round($v['pay'] / 100, 2);
			// $v['endtime'] 	= empty($v['endtime'])?'':date('Y-m-d H:i:s',$v['endtime']);
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 提现列表
	 */
	public function withdrawlist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';


		$where = [];
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "addtime <= '{$timeend}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'withdrawrecord';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr = self::$withdrawStatus;
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['endtime'] 	= empty($v['endtime'])?'':date('Y-m-d H:i:s',$v['endtime']);
			$v['statustext'] 	= $statusArr[$v['status']];
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 接单（单条数据）
	 */
	public function takeone(){
		$orderid = isset($_REQUEST['orderid']) ? intval(trim($_REQUEST['orderid'])) : '';
		if (empty($orderid)) {
			return ['code'=>100,'msg'=>'orderid empty'];
		}
		$table = 'order';
		$one = db($table)->where("id",$orderid)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$orderone = $one[0];

		if ($orderone['status'] != 1) {
			$msg = '订单状态为' . self::$statusArr[$orderone['status']] . ',不可接单';
			return ['code'=>101,'msg'=>$msg];
		}

		$r = db($table)->where("id", $orderid)->update(['status'=>2]);
		if (!$r) {
			return ['code'=>101,'msg'=>'数据库发生错误'];
		}
		return ['code'=>200,'msg'=>'接单成功'];
	}

	/**
	 * 手工完结接单（单条数据）
	 */
	public function finishorder(){
		$id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'orderid empty'];
		}
		$table = 'order';
		$one = db($table)->where("id",$id)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$orderone = $one[0];

		if ($orderone['status'] != 2) {
			$msg = '订单状态为' . self::$statusArr[$orderone['status']] . ',不可完结';
			return ['code'=>101,'msg'=>$msg];
		}

		$now = time();
		$r = db($table)->where("id", $id)->update(['status'=>3,'finishtime'=>$now]);
		if (!$r) {
			return ['code'=>101,'msg'=>'数据库发生错误'];
		}
		return ['code'=>200,'msg'=>'完结该订单成功'];
	}

	/**
	 * 更改订单状态
	 */
	public function checkandupdate(){
		// $status = 3;
		$now 	= time();
		$addtime = $now - 86400;
		$sql 	= "update `order` set `status` = 3, `finishtime` = '{$now}',`canceltype` = 5  where  `status` = 2 and addtime < '{$addtime}' ";
		echo $sql;
		$r 		= Db::execute($sql);
		return $r;
	}

	/**
	 * 提现（完成）
	 */
	public function withdrawfinish(){
		$id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}
		$table = 'withdrawrecord';
		$one = db($table)->where("id",$id)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$record = $one[0];

		if ($record['status'] != 1) {
			$msg = '单据状态为' . self::$withdrawStatus[$record['status']] . ',不可更改状态';
			return ['code'=>101,'msg'=>$msg];
		}

		$now = time();
		$r = db($table)->where("id", $id)->update(['status'=>2,'endtime'=>$now]);
		if (!$r) {
			return ['code'=>101,'msg'=>'数据库发生错误'];
		}
		return ['code'=>200,'msg'=>'确认支付成功'];
	}
	/**
	 * 提现（完成）
	 */
	public function withdrawonline(){
		$id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}
		$table = 'withdrawrecord';
		$one = db($table)->where("id",$id)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$record = $one[0];

		if ($record['status'] != 1) {
			$msg = '单据状态为' . self::$withdrawStatus[$record['status']] . ',不可更改状态';
			return ['code'=>101,'msg'=>$msg];
		}

		$now        = time();
		$realpay	= $record['pay'] * 0.99;//收取1%手续费
		$pay        = $record['pay'] * 99;//元换算成分

		$openid     = $record['openid'];
		$WappAction = new \app\wapp\action\WappAction();
		$result = $WappAction->wappwithdraw($openid, $pay, $id);//走微信商户打款
		if (isset($result['code']) && $result['code'] == 200) {
			//success
		}else{
			return ['code'=>100,'msg'=> json_encode($result)];
		}

		$note = isset($result['data']['payment_no'])? "payment_no：" . $result['data']['payment_no'] : "空日志";
		$r = db($table)->where("id", $id)->update(['status'=> 2, 'realpay'=> $realpay,'endtime'=>$now, "note" => $note]);
		if (!$r) {
			return ['code'=>101,'msg'=>'数据库发生错误，请勿重复转账'];
		}
		return ['code'=>200,'msg'=>'转账成功：' . $record['pay'] . "元"];
	}

	/**
	 * 提现（完成）
	 * 使用线上支付
	 */
	public function withdrawpay(){
		$id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}
		$table = 'withdrawrecord';
		$one = db($table)->where("id",$id)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$record = $one[0];

		if ($record['status'] != 1) {
			$msg = '单据状态为' . self::$withdrawStatus[$record['status']] . ',不可更改状态';
			return ['code'=>101,'msg'=>$msg];
		}

		$result = (new \app\wapp\action\wapp())->wappwithdraw($record['openid'],$record['pay'] * 10, $record['id']);
		if (!$result) {
			return ['code'=>101,'msg'=>'请求微信api发生错误,请勿重试'];
		}
		$now 	= time();
		$r 		= db($table)->where("id", $id)->update(['status'=>2,'endtime'=>$now]);
		if (!$r) {
			return ['code'=>101,'msg'=>'数据库发生错误'];
		}
		return ['code'=>200,'msg'=>'确认支付成功'];
	}

	/**
	 * 提现手动取消
	 */
	public function withdrawcancel(){
		$id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}
		$table = 'withdrawrecord';
		$one = db($table)->where("id",$id)->limit(1)->select();
		if (empty($one[0])) {
			return ['code'=>100,'msg'=>'get data empty'];
		}
		$record = $one[0];

		if ($record['status'] != 1) {
			$msg = '单据状态为' . self::$withdrawStatus[$record['status']] . ',不可更改状态';
			return ['code'=>101,'msg'=>$msg];
		}

		//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "数据库发生错误";
		$now = time();
		try{
			//更改提现记录信息
			$r = db($table)->where("id", $id)->update(['status'=>3,'endtime'=>$now]);
			//修改用户积分
			$pointplus = $record['paypoint'];
			$userid = $record['userid'];
			$user = Db::table('member')->where("id = '{$userid}'")->select();
			$user = $user[0];

			$newpoint = $user['point'] + $pointplus;
			$r2 = Db::table('member')->where("id = '{$userid}'")->update(['point'=>$newpoint]);

		} catch (\Exception $e) {
		   $rollback = true;
		   $msg = $e->getMessage();
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>$msg];
		}
	    Db::commit();
		return ['code'=>200,'msg'=>'取消提现单成功,积分已退还用户的账户'];
	}

	/**
	 * 接单（多条数据）
	 */
	public function takemulti(){
		$ids = isset($_REQUEST['ids']) ? trim($_REQUEST['ids']) : '';
		$array = json_decode($ids, true);
		if (empty($array)) {
			return ['code'=>100,'msg'=>'array empty'];
		}
		$count = count($array);
		$table = 'order';
		$where = " id in ('" . implode("','", $array) . "')" . ' and status != 1';
		$data = db($table)->where($where)->field("id,status")->select();
		if (!empty($data)) {
			$errorids = array_column($data, 'id');
			$msg = '以下订单:[' . implode(',', $errorids) . '] 的状态不可接单,请重新选择'; 
			return ['code'=>100,'msg'=>$msg];
		}
		$where =  " id in ('" . implode("','", $array) . "')";
		$r = db($table)->where($where)->update(['status'=>2]);
		if (!$r) {
			return ['code'=>101,'msg'=>'接单发送错误'];
		}

		return ['code'=>200,'msg'=>'接单成功,操作数量' . $count . ' ---- 成功数量'. $r];
	}


	/**
	 * 完结订单（多条数据）
	 */
	public function finishmulti(){
		$ids = isset($_REQUEST['ids']) ? trim($_REQUEST['ids']) : '';
		$array = json_decode($ids, true);
		if (empty($array)) {
			return ['code'=>100,'msg'=>'array empty'];
		}
		$count = count($array);
		$table = 'order';
		$where = " id in ('" . implode("','", $array) . "')" . ' and status != 2';
		$data = db($table)->where($where)->field("id,status")->select();
		if (!empty($data)) {
			$errorids = array_column($data, 'id');
			$msg = '以下订单:[' . implode(',', $errorids) . '] 的状态不可完结,请重新选择'; 
			return ['code'=>100,'msg'=>$msg];
		}
		$now = time();
		$where =  " id in ('" . implode("','", $array) . "')";
		$r = db($table)->where($where)->update(['status'=>3,'finishtime'=>$now]);
		if (!$r) {
			return ['code'=>101,'msg'=>'完结订单发送错误'];
		}
		Log::record($where, 'finishmulti');
		return ['code'=>200,'msg'=>'完结订单成功,操作数量' . $count . ' ---- 成功数量'. $r];
	}

	/**
	 * 订单数据
	 * 
	 */
	public function orderlistAll(){
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';


		$where = [];
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "addtime <= '{$timeend}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'order';
		$data 	= db($table)->field('id,userid,address_s,code,paypoint,relpoint,isred,redcode,note,status,addtime,addr_one,addr_two,addr_three,room,name,phone')->where($where)->order("addr_one asc,addr_two asc,code asc")->select();
		if (empty($data)) {
			return false;
		}

		return $data;
	}

	/**
	 * 订单数据
	 * 
	 */
	public function orderlistAll2(){
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : '';
		$timestart = isset($_REQUEST['timestart']) ? trim($_REQUEST['timestart']) : '';
		$timeend = isset($_REQUEST['timeend']) ? trim($_REQUEST['timeend']) : '';
		$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';


		$where = [];
		if (!empty($userid)) {
			$where[] = "userid = '{$userid}'";
		}
		if (!empty($status)) {
			$where[] = "status = '{$status}'";
		}
		if (!empty($timestart)) {
			$timestart = strtotime($timestart);
			$where[] = "addtime >= '{$timestart}'";
		}
		if (!empty($timeend)) {
			$timeend = strtotime($timeend);
			$where[] = "addtime <= '{$timeend}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'order';
		// $data 	= db($table)->field('id,userid,address_s,code,paypoint,relpoint,isred,redcode,note,status,addtime,addr_one,addr_two,addr_three,room,name,phone')->where($where)->order("addr_one asc,addr_two asc,code asc")->select();
		$data = Db::query("select a.id,a.userid,a.address_s,a.code,a.paypoint,a.relpoint,a.isred,a.redcode,a.note,a.status,a.addtime,a.addr_one,a.addr_two,a.addr_three,a.room,a.name,a.phone, b.point from `{$table}` a left join member b on a.userid = b.id where 1");
		if (empty($data)) {
			return false;
		}

		return $data;
	}

		/**
	 * 取消订单接口
	 * 
	 */
	public function cancelone(){
		$orderid 		= isset($_REQUEST['orderid']) ? trim($_REQUEST['orderid']) : '';//订单id

		if(empty($orderid)){
			return ['code'=>100,'msg'=>'orderid empty'];
		}

		$table = 'order';
		$orderone = db($table)->where("id = '{$orderid}'")->select();
		if (empty($orderone[0])) {
			return ['code'=>100,'msg'=>'order not found'];
		}
		$orderone = $orderone[0];//echo json_encode($orderone);
		if (!in_array($orderone['status'], [1,2])) {
			$msg = "订单状态为" . self::$statusArr[$orderone['status']] .",不能取消"; 
			return ['code'=>101,'msg'=>$msg];
		}
		$userid = $orderone['userid'];
		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
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
			$r 			= Db::table($table)->where('id', $orderid)->update(['status' => 4,'canceltype'=>2]);//已取消
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
			// var_dump($e->getMessage());
		   $rollback = true;
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>'update database error'];
		}
	    Db::commit();	 
		return ['code'=>200,'msg'=>'取消订单成功','result_code'=>"SUCCESS"];
	}
}

