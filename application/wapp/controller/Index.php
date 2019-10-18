<?php
namespace app\wapp\controller;

use app\wapp\action\WappAction;
use app\wapp\action\OrderAction;
use app\wapp\action\MemberAction;
use app\wapp\action\RedAction;

class Index
{
    public function index(){

        $r = (new MemberAction())->pointplus(10032);
        var_dump($r);
        // $xml['return_code'] = 'SUCCESS';
        // $xml['return_msg'] = 'OK';
        // $xml['result_code'] = 'SUCCESS';     //交易标识
        // $xml['total_fee'] = '1';//金额 单位分
        // $xml['transaction_id'] = 12344;//微信支付订单号
        // $xml['out_trade_no'] = 10000;//商户系统内部订单号
        // $xml['time_end'] = 1533179400;//支付完成时间date = date("Y-m-d");
        // $paySign = WappAction::createSign($xml);//再次生成签名
        // $xml['sign'] = $paySign;
        // // var_dump($xml);
        // $xml = (new WappAction())->ToXml($xml);//参数数组转xml
        // var_dump($xml);
        // echo $xml;
        
    }
    
    public function home(){
    	echo "home";
    }

    /**
     * 用户拉起小程序
     * @return [type]
     */
    public function login(){
        $WappAction = new WappAction();
        $r = $WappAction->checkLogin();
        return json($r);
    }

    /**
     * 用户充值
     */
    public function deposit(){
        $WappAction = new WappAction();
        $r = $WappAction->deposit();
        return json($r);
    }

    /**
     * 用户充值结果通知处理
     * (important)
     */
    public function depositnotify(){
        $WappAction = new WappAction();
        $r = $WappAction->depositnotify();
        return json($r);
    }

    /**
     * 添加订单接口
     * @return [type]
     */
    public function orderadd(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->orderadd();
        return json($r);
    }

    /**
     * 用户新增地址接口
     */
    public function addressadd(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->addressadd();
        return json($r);
    }

    /**
     * 用户修改地址接口
     */
    public function addressmodify(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->addressmodify();
        return json($r);
    }

    /**
     * 用户删除地址接口
     */
    public function addressdelete(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->addressdelete();
        return json($r);
    }

    /**
     * 用户获取自己的地址列表
     */
    public function addresslist(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->addresslist();
        return json($r);
    }

    /**
     * 用户获取优惠券
     */
    public function sendred(){
        $RedAction = new RedAction();
        $r = $RedAction->sendred();
        return json($r);
    }

    /**
     * 获取用户基本信息
     */
    public function memberinfo(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->memberinfo();
        return json($r);
    }

    /**
     * 获取用户订单
     */
    public function userorderlist(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->userOrderlist();
        return json($r);
    }

    /**
     * 获取积分使用参考
     */
    public function pointnotice(){
        $pointnotice = db('basesetting')->where("`name` = 'pointnotice'")->limit(1)->select();
        $pointnotice = empty($pointnotice[0])?'':$pointnotice[0]['content'];
        $data['pointnotice'] = $pointnotice;
        $r = array('code' => 200, 'msg'=>'success','data'=> $data);//200代表正常
        return json_encode($r);
    }

    /**
     * 获取服务条款
     */
    public function servicenotice(){
        $servicenotice = db('basesetting')->where("`name` = 'servicenotice'")->limit(1)->select();
        $servicenotice = empty($servicenotice[0])?'':$servicenotice[0]['content'];
        $servicenotice = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $servicenotice);
        $data['servicenotice'] = $servicenotice;

        $r = array('code' => 200, 'msg'=>'success','data'=> $data);//200代表正常
        return json_encode($r);//JSON_UNESCAPED_UNICODE
    }

    /**
     * 获取优惠券数量
     */
    public function getrednumber(){
        $RedAction = new RedAction();
        $r = $RedAction->getrednumber();
        return json($r);       
    }

    /**
     * 取消订单
     */
    public function ordercancel(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->ordercancel();
        return json($r);
    }

    /**
     * 完结订单
     */
    public function orderfinish(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->orderfinish();
        return json($r);
    }

    /**
     * 提现订单
     */
    public function withdraw(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->withdraw();
        return json($r);        
    }

    /**
     * 骑手获取行程
     */
    public function route(){
        $RouteAction = new \app\wapp\action\RouteAction();
        $r = $RouteAction->route();
        return json($r); 
    }

    /**
     * 骑手更新行程
     */
    public function routeup(){
        $RouteAction = new \app\wapp\action\RouteAction();
        $r = $RouteAction->routeup();
        return json($r); 
    }

    /**
     * 获取小程序幻灯片
     */
    public function viewpager(){
        $RouteAction = new \app\index\action\SettingAction();
        $r = $RouteAction->getviewpager();
        return json($r); 
    }

    /**
     * 用户提出意见
     */
    public function opinion(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->opinion();
        return json($r);

    }

    /**
     * 用户更新昵称
     */
    public function updatemember(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->updatemember();
        return json($r);
    }

    /**
     * 用户更新手机
     */
    public function updatephone(){
        $MemberAction = new MemberAction();
        $r = $MemberAction->updatephone();
        return json($r);
    }

    /**
     * 用户通过兑换码获取优惠券
     */
    public function giftcode(){
        $RedAction = new RedAction();
        $r = $RedAction->codetored();
        return json($r);
    }

    /**
     * 获取用户优惠券分页数据
     */
    public function userredlist(){
        $RedAction = new RedAction();
        $r = $RedAction->userRedList();
        return json($r);
    }

}
