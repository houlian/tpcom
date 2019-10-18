<?php
/**
 * 渲染层基础类
 *
 * 
 */
namespace app\index\controller;
use think\Controller;

class Baseview extends controller {

	protected $_username	= '';
	protected $_userid		= 0;

	public function __construct(){
        parent::__construct();
		$request = request();
		//session存在时，不需要验证的权限
		$not_check = array('Index/login','Index/logout','Index/userlogin');
		//当前操作的请求 模块名/方法名
		$controller = $request->controller();
		$action = $request->action();
		if(!in_array($controller .'/'. $action, $not_check)){
			//session不存在时，不允许直接访问
			if(!session('?_userId')){
				//未登陆跳转
				$this->redirect('index/login');
			}
		}

		$this->_username	= session('?_userName') ? session('_userName') : "";
		$this->_userid		= session('_userId') ? session('_userId') : 0;
        $projectname = config('projectname');
        $this->assign([
            '_username' 	=> $this->_username,
            '_userid'	 	=> $this->_userid,
            '_projectname'	=>	$projectname,
            '_controller'	=>	strtolower($controller),
            '_action'		=>	$action,
        ]);
	}	
} 