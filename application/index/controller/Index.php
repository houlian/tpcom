<?php
namespace app\index\controller;
use app\index\controller\Baseview;
use app\index\action\UserAction;
use app\index\action\RedAction;
use think\Log;

class Index extends Baseview
{
    public function index(){

        $count  = db('member')->field('count(1) as total')->limit(1)->find();
        $member_count = $count['total'];
        $this->assign([
            'member_count'     => $member_count,
        ]);
        return view('home');
    }

     public function test(){

        echo md5('FyingShan');
        // $number = $_REQUEST['number'];
        // $RedAction = new RedAction();
        // // echo $RedAction->numberToCode($number);
        // $r = $RedAction->createRedeemCode($number);
        // // $r = $RedAction->getAvailableCode($number);
        // var_dump($r);
        // Log::record('goods ', 'info');
        // phpinfo();
        // return;
        
        // ini_set("display_errors","On");
        // $date = date("Y-m-d");
        // $cachename = 'RED_123';
        // // $cachename = 'RED_' .$date;
        // $num = \think\Cache::get($cachename);
        // echo "目前缓存:";
        // var_dump($num);
        // \think\Cache::dec($cachename);
        // $num = \think\Cache::get($cachename);
        // echo "目前缓存:";
        // var_dump($num);

    }
    public function test1(){
        // phpinfo();
        // return;
        // ini_set("display_errors","On");
        // $date = date("Y-m-d");
        // $cachename = 'RED_' .$date;
        // // $cachename = 'RED_' .$date;
        // $num = \think\Cache::get($cachename);
        // echo "目前缓存:";
        // var_dump($num);
        // $num = (new \app\index\action\RedAction())->calred();
        // if ($num === false) {//缓存不存在重新设置缓存
        // }
        // if ($num <= 0) {
        //     echo  json_encode(['code'=>200,'msg'=>'优惠券被抢光了,下次早点来','type'=>2,'redcode'=>'']);
        // }
        var_dump($num);
        // $r2 = (new \app\wapp\action\MemberAction())->pointplus(10052);
        // var_dump($r2);
        // $date = date("Y-m-d");
        // $cachename = 'DDD_' .$date;

        // \think\Cache::dec($cachename);//缓存自减
        // $s = \think\Cache::set($cachename,66,86400);
        // var_dump($s);
        // // var_dump($s);
        // $r = \think\Cache::get($cachename);
        // echo $cachename . '--' . $r;

    	// echo url('index/index/userLogin');
    	// $a = input('request.id');
    	// var_dump($a);
    	// echo md5('123456');
        // $order = new \app\index\action\RedAction();
        // $a = $order->createred(100);
        // echo json_encode($a);
        // $wapp = new \app\wapp\action\WappAction();
        // $a = $wapp->wappwithdraw('oK4MY48XFo0xKnpGJU2ZDcvnvMkk',1,1);
    }

    public function test2(){
                $wapp = new \app\wapp\action\WappAction();
        $a = $wapp->depositnotify();
        var_dump($a);
        // $str ="17:00:00";
        // echo time() . "<br>";
        // echo date("Y-m-d H:i:s") . "<br>";
        // echo strtotime($str) . "<br>";
        // echo date("Y-m-d H:i:s",strtotime($str)) . "<br>";

        // $cachename = 'RED_' .$date;

        // \think\Cache::dec($cachename);//缓存自减
        
        // $r = \think\Cache::get($cachename);
        // echo $cachename . '--' . $r;
    }

    public function login(){
    	if (session('?_userId')) {
    		$this->redirect('index/index');
    	}
    	return view('login');
    }



    /**
     * @actName:登陆验证
     * @return [type] [description]
     */
    public function userlogin(){
    	$user = new UserAction();
    	$r = $user->checkLogin();
    	return json($r);
    }

    /**
     * @actName:登陆验证
     * @return [type] [description]
     */
    public function logout(){
    	// 清除session（当前作用域）
		session(null);
		$this->redirect('index/login');
    }
}
