<?php
namespace app\index\controller;
use app\index\controller\Baseview;
use app\index\action\MemberAction;

class Member extends Baseview
{
    /**
     * 用户列表
     * @return [type]
     */
    public function index(){
        $count  = db('member')->field('count(1) as total')->limit(1)->find();
        $member_count = $count['total'];
        $this->assign([
            'member_count'     => $member_count,
        ]);
        return view('index/home');
    }

    public function test(){
    	echo url('index/index/userLogin');
    	// $a = input('request.id');
    	// var_dump($a);
    	// echo md5('123456');
    }

    /**
     * 数据接口
     * 获取用户列表（分页）
     */
    public function memberlist(){

        $member = new MemberAction();
        $r = $member->memberlist();
        return json($r);
    }

    /**
     * 骑手行程
     */
    public function route(){
        return view('index/route');
    }

    /**
     * 骑手行程
     */
    public function opinion(){
        return view('index/opinion');
    }

    /**
     * 数据接口
     * 获取行程（分页）
     */
    public function routelist(){
        $member = new MemberAction();
        $r = $member->routelist();
        return json($r);
    }

    /**
     * 数据接口
     * 获取行程（分页）
     */
    public function opinionlist(){
        $member = new MemberAction();
        $r = $member->opinionlist();
        return json($r);
    }

    /**
     * 用户编辑
     */
    public function edit(){
        $id     = isset($_REQUEST['id']) ?intval(trim($_REQUEST['id'])) : '';
        if (empty($id)) {

        }
        $member     = new MemberAction();
        $memberinfo = $member->getone($id);
        $this->assign("member",$memberinfo);
        return view('index/memberedit');
    }

    /**
     * 用户编辑
     */
    public function membermodify(){
        $member = new MemberAction();
        $r = $member->membermodify();
        return json($r);
    }

    /**
     * 赠送优惠券
     */
    public function giftred(){
        $member = new MemberAction();
        $r = $member->giftred();
        return json($r);
    }
}
