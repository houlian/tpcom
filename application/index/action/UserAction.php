<?php
namespace app\index\action;

use think\Model;
use app\index\model\UserModel;

class UserAction extends Model{

	public function checkLogin(){
		$username 	= isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
		$password 	= isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';

		if($username == ''){
			return ['code'=>100,'msg'=>'name empty'];
		}

		if($password == ''){
			return ['code'=>101,'msg'=>'pwd emtpy'];
		}


		$usermodel = new UserModel();
		$password = md5($password);
		$loginfo = $usermodel->get(['name'=>$username,'password'=>$password]);
        if(empty($loginfo)){
         	return ['code'=>102,'msg'=>'用户名或密码错误'];
        }

        ## 登录成功写session
        session('_userId', $loginfo['id']);
        session('_userName', $loginfo['name']);
        session('_type', 1);

        return ['code'=>200,'msg'=>'登陆成功'];//200代表正常返回
	}

}

