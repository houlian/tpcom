<?php
namespace app\index\controller;
use app\index\controller\Baseview;
use app\index\action\SettingAction;

class Setting extends Baseview
{

    static $addr = array(
                        '北区' =>array(
                            '梅园' =>array(1,2,3,4,5),
                            '桂园' =>array(6,7,8,9,10,11),
                            '榴园' =>array(12,13,14,15,16,17),
                            '桃园' =>array(18,19,20,21),
                            '李园' =>array(22,23,24,25),
                            '杏园' =>array(26,27,28,29),
                            '桔园' =>array(30,31,32,33),
                            ),
                        '南区' => array(
                            '浩苑' => array(60,61,62,63), 
                            '润苑' => array(64,65,66,67,68,69), 
                            '鸿苑' => array(70,71,72), 
                            '澈苑' => array(73,74,75,76), 
                            '清苑' => array(77,78,79,80), 
                            '涓苑' => array(81,82,83,84), 
                            '溪苑' => array(85,86), 
                            '瀚苑' => array(91,92), 
                            ) ,
                        );

    public function index(){
        $addr = self::$addr;
        echo json_encode($addr,JSON_UNESCAPED_UNICODE);//避免将中文转Unicode
        echo "<br>";
        // print_r($addr);
    }

    /**
     * 全局参数配置
     * @return [type] [description]
     */
    public function param(){
        $name = "baseconf";
        $info = db('basesetting')->where("`name` = '{$name}'")->limit(1)->select();
        $info = empty($info[0])?'':$info[0]['content'];
        $array = [];
        if (empty($info)) {

        }else{
            $array = json_decode($info,true);

        }
        $this->assign("array",$array);
        return view('param');
    }

    /**
     * 修改参数配置
     */
    public function parammodify(){
        $setting = new SettingAction();
        $r = $setting->parammodify();
        return json($r);

    }

    /**
     * 优惠券参数配置
     * @return [type] [description]
     */
    public function redconf(){
        $info = (new \app\index\action\RedAction())->redconf();
        $this->assign("info",$info);
        return view('redconf');
    }

    /**
     * 修改参数配置
     */
    public function redconfmodify(){
        $setting = new SettingAction();
        $r = $setting->redconfmodify();
        return json($r);
    }

    /**
     * 园区地址配置
     */
    public function address(){
        $address = db('basesetting')->where("`name` = 'address'")->limit(1)->select();
        // var_dump($address);return;
        $address = empty($address[0])?'':$address[0]['content'];
        $this->assign("address",$address);
    	return view('address');
    }

    /**
     * 修改地址配置
     */
    public function addressmodify(){
        $setting = new SettingAction();
        $r = $setting->addressmodify();
        return json($r);

    }

    /**
     * 积分使用配置
     */
    public function pointnotice(){
        $pointnotice = db('basesetting')->where("`name` = 'pointnotice'")->limit(1)->select();
        // var_dump($address);return;
        $pointnotice = empty($pointnotice[0])?'':$pointnotice[0]['content'];
        $this->assign("pointnotice",$pointnotice);
        return view('pointnotice');
    }

    /**
     * 修改积分配置
     */
    public function pointnoticemodify(){
        $setting = new SettingAction();
        $r = $setting->pointnoticemodify();
        return json($r);

    }

    /**
     * 服务条款配置
     */
    public function servicenotice(){
        $servicenotice = db('basesetting')->where("`name` = 'servicenotice'")->limit(1)->select();
        // var_dump($address);return;
        $servicenotice = empty($servicenotice[0])?'':$servicenotice[0]['content'];
        $this->assign("servicenotice",$servicenotice);
        return view('servicenotice');
    }

    /**
     * 服务条款配置
     */
    public function servicenoticemodify(){
        $setting = new SettingAction();
        $r = $setting->servicenoticemodify();
        return json($r);

    }


    public function test(){
        $setting = new SettingAction();
        $r = $setting->getAddressJson();
    	// echo url('index/index/userLogin');
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
     * 用户下单频率7天
     */
    public function rate(){
        $today = date("Y-m-d");
        $this->assign("today",$today);
        return view('rate');
    }

    /**
     * 用户下单频率7天
     */
    public function ratelist(){
        $setting = new SettingAction();
        $r = $setting->ratelist();
        return json($r);
    }

    /**
     * 幻灯片
     */
    public function viewpager(){
        return view('viewpager');
    }

    /**
     * 幻灯片列表
     */
    public function imagelist(){
        $setting = new SettingAction();
        $r = $setting->imagelist();
        return json($r);
    }

    /**
     * 新增幻灯片
     */
    public function addimage(){
        return view('addimage');
    }

    /**
     * 新增幻灯片(post)
     */
    public function imageadd(){
        $setting = new SettingAction();
        $r = $setting->imageadd();
        return json($r);   
    }

    /**
     * 新增幻灯片(save)
     */
    public function imagesave(){
        $setting = new SettingAction();
        $r = $setting->imagesave();
        return json($r);   
    }

    /**
     * 修改幻灯片状态
     */
    public function imagestatus(){
        $setting = new SettingAction();
        $r = $setting->imagestatus();
        return json($r);  
    }

    /**
     * 幻灯片编辑
     */
    public function imagemodify(){
        $id = isset($_REQUEST['id']) ? intval(trim($_REQUEST['id'])) : 0;
        if (empty($id)) {
            # code...
        }

        $data = db('viewpager')->where("id = '{$id}'")->select();
        $image = $data[0];
        $this->assign("image",$image);
        return view('editimage');
    }

    /**
     * 编辑幻灯片(post)
     */
    public function imageedit(){
        $setting = new SettingAction();
        $r = $setting->imageedit();
        return json($r);   
    }
}
