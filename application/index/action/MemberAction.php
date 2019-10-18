<?php

namespace app\index\action;

use think\Model;
use app\index\model\MemberModel;
use app\index\model\LogModel;
use think\Db;

class MemberAction extends Model{


	/**
	 * 用户列表
	 */
	public function memberlist(){
		$page     = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit    = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$id       = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$black    = isset($_REQUEST['black']) ? trim($_REQUEST['black']) : '';
		$name     = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
		$isdriver = isset($_REQUEST['isdriver']) ? trim($_REQUEST['isdriver']) : '';
		$mobile   = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';

		$where = [];
		if (!empty($name)) {
			$where[] = "name = '{$name}'";
		}
		if (!empty($id)) {
			$where[] = "id = '{$id}'";
		}
		if (!empty($black)) {
			$where[] = "status = '{$black}'";
		}if (!empty($isdriver)) {
			$type = $isdriver == 2?1:0;
			$where[] = "type = '{$type}'";
		}
		if (!empty($mobile)) {
			$where[] = "mobile like '{$mobile}%'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		// $r = db('member')->where(1)->select();

		$memberModel = new MemberModel();
		$data = $memberModel->where($where)->page($page, $limit)->order("id desc")->select();//->fetchSql(true)
		$data = collection($data)->toArray();//对象数组转二维数组
		$count = $memberModel->field('count(1) as total')->where($where)->limit(1)->find();
		$count = $count->toArray()['total'];

		foreach ($data as &$v) {
			$v['regtime'] 	= empty($v['regtime'])?'':date('Y-m-d',$v['regtime']);
			$v['status'] 	= $v['status'] == 2?'黑名单':'正常';
			$v['typetext'] 	= $v['type'] == 1?'是':'否';
			empty($v['name']) && $v['name'] = '未授权';
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 获取member
	 */
	public function getone($userid){
		$table = 'member';
		$memberModel = new MemberModel();
		$one = $memberModel->get($userid);
		return empty($one)? false : $one;
	}

	/**
	 * 行程列表
	 */
	public function routelist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;

		$where = 1;
		$table 	= 'route';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$array = json_decode($v['content'],true);
			$route = '';
			foreach ($array as $a) {
				if($a['s'] == 2){
					$route .= $a['a'] . ":" . '<span class="color_ok">已送达</span>&nbsp;';
				}else{
					$route .= $a['a'] . ":" . '==&nbsp;';
				}
			}
			$v['route'] = $route;
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 意见
	 */
	public function opinionlist(){
		$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;

		$where = 1;
		$table 	= 'opinion';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 用户编辑
	 */
	public function membermodify(){
		$id 	= isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : 0;
		$status = isset($_REQUEST['status']) ? intval(trim($_REQUEST['status'])) : 2;//2为黑名单
		$type 	= isset($_REQUEST['type']) ? intval(trim($_REQUEST['type'])) : 0;
		$point 	= isset($_REQUEST['point']) ? intval(trim($_REQUEST['point'])) : '0';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}

		$one = $this->getone($id);
		$data = [];
		if ($status != $one['status']) {
			if ($status == 1) {
				$data['status'] = 1;
				$data['blacktime'] = 0;
			}else{
				$data['status'] = 2;
				$data['blacktime'] = time();
			}
		}
		if ($type != $one['type']) {
			$data['type'] = $type;
		}
		if($point != $one['point']){
			$data['point'] = $point;
		}
		if (empty($data)) {
			return ['code'=>200,'msg'=>'没作任何修改'];
		}
		$r = db('member')->where("id = '{$id}'")->update($data);
		$LogModel = new LogModel();
		$content = json_encode(['old'=>$one, 'new' => $data]);
		$LogModel->savelog('user', $content);

		return ['code'=>200,'msg'=>'保存成功'];
	}

	/**
	 * 后台赠送给用户的优惠券
	 * 类型为手动生成，无兑换码，领取时间=生成时间
	 * @return [type] [description]
	 */
	public function giftred()
	{
		$userid = isset($_REQUEST['userid']) ? intval(trim($_REQUEST['userid'])) : 0;
		$count = isset($_REQUEST['count']) ? intval(trim($_REQUEST['count'])) : 0;
		$expiration = isset($_REQUEST['expiration']) ? trim($_REQUEST['expiration']) : 0;

		if (empty($count) || empty($expiration) || empty($userid)) {
			return array('code' => 100, 'msg'=>'param error');
		}elseif ($count < 1 || $count > 10) {
			return array('code' => 100, 'msg'=>'数量填写错误');
		}elseif(strtotime($expiration) < time() ){
			return array('code' => 100, 'msg'=>'过期时间请大于此刻');
		}

		$length = strlen($count);
		$now 	= time();
		$data 	= [];
		$d 		= ['userid' => $userid, 'type' => 2, 'status' => 2, 'addtime' => $now, 'gettime' => $now, 'expiration' => strtotime($expiration)];
		for ($i=1; $i <= $count; $i++) {
			$d['redcode'] = 'R' . $now . str_pad($i,$length,'0',STR_PAD_LEFT);
			$data[] = $d;
		}

		$r = Db::name('red')->insertAll($data);//插入所有数据

		return array('code' => 200, 'msg'=>'赠送' . $count . '张优惠券成功');
	}
}

