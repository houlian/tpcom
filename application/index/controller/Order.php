<?php
namespace app\index\controller;
use app\index\controller\Baseview;
use app\index\action\OrderAction;
use app\index\action\RedAction;

class Order extends Baseview{

    /**
     * 代发订单列表
     * @return [type]
     */
    public function index(){
        return view('order');
    }

    /**
     * 数据接口
     * 获取订单列表（分页）
     */
    public function orderlist(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->orderlist();
        return json($r);
    }

    /**
     * 订单详情
     */
    public function orderinfo(){
        $id = trim($_REQUEST['id']);
        if (empty($id)) {
            echo "id is empty";
            return ;
        }
        $order = db('order')->where("`id` = '{$id}'")->limit(1)->select();
        $order = empty($order[0])?'':$order[0];
        $order['statustext'] = OrderAction::$statusArr[$order['status']];
        $this->assign("order",$order);
        return view('orderinfo');        
    }

    /**
     * 取消订单(单)
     */
    public function cancelone(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->cancelone();
        return json($r);
    }

    /**
     * 接单(单)
     */
    public function takeone(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->takeone();
        return json($r);
    }

    /**
     * 接单(多)
     */
    public function takemulti(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->takemulti();
        return json($r);
    }

    //优惠券列表
    public function red(){
        return view('red');
    }

    //定制优惠券
    public function addred(){
        return view('addred');
    }

    /**
     * 充值记录
     */
    public function deposit(){
        $status = OrderAction::$desportStatus;
        $this->assign([
            'status'      => $status,
        ]);
        return view('deposit');
    }

    /**
     * 充值记录数据接口
     */
    public function depositlist(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->depositlist();
        return json($r);
    }

    /**
     * 提现记录
     */
    public function withdraw(){
        $status = OrderAction::$withdrawStatus;
        $this->assign([
            'status'      => $status,
        ]);
        return view('withdraw');
    }

    /**
     * 提现完成
     */
    public function withdrawfinish(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->withdrawfinish();
        return json($r);
    }
    /**
     * 提现完成
     */
    public function withdrawonline(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->withdrawonline();
        return json($r);
    }

    /**
     * 提现取消
     */
    public function withdrawcancel(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->withdrawcancel();
        return json($r);
    }


    /**
     * 提现记录数据接口
     */
    public function withdrawlist(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->withdrawlist();
        return json($r);
    }

    /**
     * 数据接口
     * 获取优惠券列表（分页）
     */
    public function redlist(){
        $RedAction = new RedAction();
        $r = $RedAction->redlist();
        return json($r);
    }

    /**
     * 生成定制优惠券
     */
    public function addredaction(){
        $RedAction = new RedAction();
        $r = $RedAction->createRedOfSpecial();
        return json($r);
    }

    /**
     * 手工完结
     */
    public function finishorder(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->finishorder();
        return json($r);
    }

    /**
     * 手工完结(批量)
     */
    public function finishmulti(){
        $OrderAction = new OrderAction();
        $r = $OrderAction->finishmulti();
        return json($r);
    }

    /**
     * 订单导出
     */
    public function orderexport(){
        header("Content-Type: text/html; charset=utf-8");
        vendor("PHPExcel.PHPExcel");
        // require_once(APP_PATH . "../vendor/PHPExcel/PHPExcel.php");
        // $zip = new \ZipArchive();
        // phpinfo();exit();
        set_time_limit(0);
        ini_set('memory_limit','500M'); 
        $OrderAction = new OrderAction();
        $data = $OrderAction->orderlistAll();
        if (empty($data)) {
            echo "<strong>data empty</strong>";
            return ;
        }
  
        // Create new PHPExcel object  
        $objPHPExcel = new \PHPExcel();

        $headnames = array('id','用户id','取件码','使用积分','使用优惠券','优惠券码','实际使用积分','取件点','收货点-南北区','园区','楼号','宿舍','姓名','手机号','备注','时间','状态');//-----------------表头
        $objPHPExcel->setActiveSheetIndex(0);//---------------------------------------默认操作工作表0
        $objPHPExcel->getActiveSheet()->setTitle("时间");//-------------------------设置工作表名称

        // $objPHPExcel->getActiveSheet()->mergeCells('A1:L1');//合并单元格
        // $objPHPExcel->getActiveSheet()->setCellValue('A1' , "订单列表");//------设置表头
        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

        $c = 65;//A的ASCII码
        $k = 1;
        foreach ($headnames as $name) {
            //echo $c . $k .':' . $c . ($k+1);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $name);//------设置表头
        }
        $k++;

        $statusArr = OrderAction::$statusArr;
        foreach ($data as $v) {
            $v['addtime']   = empty($v['addtime'])?'':date('Y-m-d',$v['addtime']);
            $v['status']    = $statusArr[$v['status']];
            $v['isred']     = $v['isred'] == 1?"否":"是";

            $c = 65;//A的ASCII码
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['id']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['userid']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['code']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['paypoint']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['isred']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['redcode']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['relpoint']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['address_s']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_one']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_two']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_three']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['room']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['phone']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['note']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addtime']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['status']);
            $k++;
        }

        $title = 'jiajia' . date("Y-m-d") . '.xls';
        // $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//-------------------设置字体
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);//----------------------------设置字的大小
        // var_dump($objPHPExcel);exit();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        if (false) {
            header('pragma:public'); 
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename={$title}");
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $objWriter->save("php://output");//-----------------over
        }

        $attachpath     = PUBLIC_PATH ;//附件地址(本地)
        $cenpath        = 'attachment/exceldir/' . date("Ym") . "/";
        $floderpath     = $attachpath . $cenpath;
        $realpath       = $attachpath . $cenpath .  $title;//本地保存路径
        if(!file_exists($floderpath)) {
            // echo $floderpath;
            if(mkdir($floderpath,0777, true)) {
                chmod($floderpath,0777);
            }else{
                return json(array('code'=>"100","msg"=>"创建文件夹出错"));

            }
        } else {
            //echo "该文件夹已存在";
        }

        $objWriter->save($realpath);

        $file = $realpath;
        if(is_file($file)){
            $length = filesize($file);
            $type = mime_content_type($file);
            $showname =  ltrim(strrchr($file,'/'),'/');
            header("Content-Description: File Transfer");
            header('Content-type: ' . $type);
            header('Content-Length:' . $length);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $showname . '"');
            }
            readfile($file);
        } else {
            $this->error('源文件不存在！');
        }
    }


    public function orderexport2(){
        header("Content-Type: text/html; charset=utf-8");
        vendor("PHPExcel.PHPExcel");
        // require_once(APP_PATH . "../vendor/PHPExcel/PHPExcel.php");
        // $zip = new \ZipArchive();
        // phpinfo();exit();
        set_time_limit(0);
        ini_set('memory_limit','500M'); 
        $OrderAction = new OrderAction();
        $data = $OrderAction->orderlistAll2();
        if (empty($data)) {
            echo "<strong>data empty</strong>";
            return ;
        }
  
        // Create new PHPExcel object  
        $objPHPExcel = new \PHPExcel();

        $headnames = array('id','用户id','取件码','使用积分','使用优惠券','优惠券码','实际使用积分','取件点','收货点-南北区','园区','楼号','宿舍','姓名','手机号','备注','时间','状态','积分余额');//-----------------表头
        $objPHPExcel->setActiveSheetIndex(0);//---------------------------------------默认操作工作表0
        $objPHPExcel->getActiveSheet()->setTitle("时间");//-------------------------设置工作表名称

        // $objPHPExcel->getActiveSheet()->mergeCells('A1:L1');//合并单元格
        // $objPHPExcel->getActiveSheet()->setCellValue('A1' , "订单列表");//------设置表头
        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

        $c = 65;//A的ASCII码
        $k = 1;
        foreach ($headnames as $name) {
            //echo $c . $k .':' . $c . ($k+1);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $name);//------设置表头
        }
        $k++;

        $statusArr = OrderAction::$statusArr;
        foreach ($data as $v) {
            $v['addtime']   = empty($v['addtime'])?'':date('Y-m-d',$v['addtime']);
            $v['status']    = $statusArr[$v['status']];
            $v['isred']     = $v['isred'] == 1?"否":"是";

            $c = 65;//A的ASCII码
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['id']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['userid']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['code']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['paypoint']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['isred']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['redcode']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['relpoint']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['address_s']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_one']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_two']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addr_three']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['room']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['phone']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['note']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['addtime']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['status']);
            $objPHPExcel->getActiveSheet()->setCellValue(chr($c++) . $k , $v['point']);
            $k++;
        }

        $title = 'jiajia' . date("Y-m-d") . '.xls';
        // $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//-------------------设置字体
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);//----------------------------设置字的大小
        // var_dump($objPHPExcel);exit();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        if (false) {
            header('pragma:public'); 
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename={$title}");
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $objWriter->save("php://output");//-----------------over
        }

        $attachpath     = PUBLIC_PATH ;//附件地址(本地)
        $cenpath        = 'attachment/exceldir/' . date("Ym") . "/";
        $floderpath     = $attachpath . $cenpath;
        $realpath       = $attachpath . $cenpath .  $title;//本地保存路径
        if(!file_exists($floderpath)) {
            // echo $floderpath;
            if(mkdir($floderpath,0777, true)) {
                chmod($floderpath,0777);
            }else{
                return json(array('code'=>"100","msg"=>"创建文件夹出错"));

            }
        } else {
            //echo "该文件夹已存在";
        }

        $objWriter->save($realpath);

        $file = $realpath;
        if(is_file($file)){
            $length = filesize($file);
            $type = mime_content_type($file);
            $showname =  ltrim(strrchr($file,'/'),'/');
            header("Content-Description: File Transfer");
            header('Content-type: ' . $type);
            header('Content-Length:' . $length);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $showname . '"');
            }
            readfile($file);
        } else {
            $this->error('源文件不存在！');
        }
    }
}
