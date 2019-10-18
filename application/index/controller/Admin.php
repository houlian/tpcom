<?php
namespace app\index\controller;

use app\index\action\ConfigAction;

class Admin extends Baseview
{
	static $statusArr = ['0'=>'选择状态','1'=>'正常','2'=>'黑名单'];


    public function index()
    {
    	$ConfigAction = new ConfigAction();
    	$school = $ConfigAction->schoolConfig();

        $this->assign([
            'school'     => $school,
        ]);
        return view();
    }
    
    public function datalist()
    {
    	$page = isset($_REQUEST['page']) ? intval(trim($_REQUEST['page'])) : 1;
		$limit = isset($_REQUEST['limit']) ? intval(trim($_REQUEST['limit'])) : 15;
		$school = isset($_REQUEST['school']) ? intval(trim($_REQUEST['school'])) : '';

		$where = [];
		if (!empty($status)) {
			$where[] = "school = '{$school}'";
		}
		$where = empty($where)?"":implode(" and ", $where);

		$table 	= 'admin';
		$data 	= db($table)->where($where)->page($page, $limit)->order("id desc")->select();
		$count 	= db($table)->field('count(1) as total')->where($where)->limit(1)->find();
		$count 	= $count['total'];

		$statusArr 		= self::$statusArr;
		$ConfigAction 	= new ConfigAction();
    	$school 		= $ConfigAction->schoolConfig();
    	$school[0]		= '全部校区';
		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
			$v['status'] 	= $statusArr[$v['status']];
			$v['school'] 	= $school[$v['school']];
			$v['type'] 		= $v['type'] == 1?'超级管理员':'管理员';
		}
		unset($v);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
    }


    /**
     * 管理员编辑
     */
    public function edit(){
        $id     = isset($_REQUEST['id']) ?intval(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
        	$item = [
        		'id' => '',
        		'name' => '',
        		'password' => '',
        		'nickname' => '',
        		'phone' => '',
        	];
        }else{
        	$item     = db('admin')->where('id', $id)->find();
        }
        $ConfigAction = new ConfigAction();
    	$school = $ConfigAction->schoolConfig();

        $this->assign([
            'school'     => $school,
            'item'     		=> $item,
        ]);
        return view();
    }

    public function datasave()
    {
    	$id 		= isset($_REQUEST['id']) ?trim($_REQUEST['id']) : '';
		$name 		= isset($_REQUEST['name']) ?trim($_REQUEST['name']) : '';
		$password 	= isset($_REQUEST['password']) ?trim($_REQUEST['password']) : '';
		$school 	= isset($_REQUEST['school']) ?trim($_REQUEST['school']) : '';
		$nickname 	= isset($_REQUEST['nickname']) ?trim($_REQUEST['nickname']) : '';
		$phone 		= isset($_REQUEST['phone']) ?trim($_REQUEST['phone']) : '';

		if (empty($school)) {
			return ['code'=>100,'msg'=>'school empty'];
		}
		if ($id) {

		}else{
			if (empty($name)) {
				return ['code'=>100,'msg'=>'name empty'];
			}elseif (!$password) {
				return ['code'=>100,'msg'=>'password empty'];
			}
		}
		

		$now = time();
		$table 	= "admin";
		$data = ['school' => $school, 'nickname' => $nickname, 'phone' => $phone];

		if ($id) {
			if (!empty($password)) {
				if (strlen($password) < 6 || strlen($password) > 15) {
					return ['code'=>100,'msg'=>'密码长度请在6~15之间'];
				}
				$data['password'] = md5($password);
			}
			db($table)->where('id', $id)->updata($data);
		}else{
			if (strlen($password) < 6 || strlen($password) > 15) {
				return ['code'=>100,'msg'=>'密码长度请在6~15之间'];
			}

			$data['addtime'] = $now;
			$data['name'] = $name;
			$data['password'] = md5($password);
			$data['type'] = 2;//校区管理员
			db($table)->insert($data);
		}
		return ['code'=>200,'msg'=>'保存成功'];
    }

}
