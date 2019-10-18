<?php

namespace app\index\action;

use think\Model;
use think\Db;

class SettingAction{

	public function getAddressJson(){
		$address = db('basesetting')->where("`name` = 'address'")->limit(1)->select();
        $address = $address[0]['content'];
        $array = json_decode($address, true);
        $a = [];
        $a['base'] = array_keys($array);
        foreach ($array as $key=> $value) {
        	$a[$key] = array_keys($value);
        	foreach ($value as $k => $v) {
        		$a[$k] = $v;
        	}
        }
        // echo json_encode($a,JSON_UNESCAPED_UNICODE);
        return $a;
	}

	/**
	 * 修改地址配置
	 */
	public function addressmodify(){
		$address = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : "";
		if (empty($address)) {
			return ['code'=>100,'msg'=>'address empty'];
		}
		$table = "basesetting";
		$name = "address";
		$isexist = Db::query("select * from `{$table}` where name=?",[$name]);
		if ($isexist) {
			$sql = "update `{$table}` set content = '{$address}' where name = '{$name}'";
		}else{
			$sql = "insert into `{$table}` (name,content) value ('{$name}','{$address}')";
		}

		$q = Db::execute($sql);
		if ($q) {
			return ['code'=>200,'msg'=>'success'];
		}
		return ['code'=>102,'msg'=>'update error'];
	}

	/**
	 * 修改积分使用参考
	 */
	public function pointnoticemodify(){
		$pointnotice = isset($_REQUEST['pointnotice']) ? trim($_REQUEST['pointnotice']) : "";
		if (empty($pointnotice)) {
			return ['code'=>100,'msg'=>'pointnotice empty'];
		}
		$table = "basesetting";
		$name = "pointnotice";
		$isexist = Db::query("select * from `{$table}` where name=?",[$name]);
		if ($isexist) {
			$sql = "update `{$table}` set content = '{$pointnotice}' where name = '{$name}'";
		}else{
			$sql = "insert into `{$table}` (name,content) value ('{$name}','{$pointnotice}')";
		}

		$q = Db::execute($sql);
		if ($q) {
			return ['code'=>200,'msg'=>'success'];
		}
		return ['code'=>102,'msg'=>'内容未做任何改变'];
	}

	/**
	 * 修改服务条款
	 */
	public function servicenoticemodify(){
		$d = isset($_REQUEST['data']) ? $_REQUEST['data'] : "";
		if (empty($d)) {
			return ['code'=>100,'msg'=>'data empty'];
		}
		$table = "basesetting";
		$name = "servicenotice";
		$servicenotice = $d['content'];
		$isexist = Db::query("select * from `{$table}` where name=?",[$name]);
		if ($isexist) {
			$sql = "update `{$table}` set content = '{$servicenotice}' where name = '{$name}'";
		}else{
			$sql = "insert into `{$table}` (name,content) value ('{$name}','{$servicenotice}')";
		}

		$q = Db::execute($sql);
		if ($q) {
			return ['code'=>200,'msg'=>'success'];
		}
		return ['code'=>102,'msg'=>'条款未做任何改变'];
	}

	/**
	 * 统计列表
	 */
	public function ratelist(){
		$timeindex = isset($_REQUEST['timeindex']) ? trim($_REQUEST['timeindex']) : '';
		$days = isset($_REQUEST['days']) ? intval(trim($_REQUEST['days'])) : 7;

		if(empty($timeindex)){
			$timeindex = strtotime(date("Y-m-d"));
		}else{
			$timeindex = strtotime($timeindex);
		}
		$timestart = $timeindex - 86400 * $days;
		$sql = "select userid, count(1) as rate,sum(relpoint) as payall from `order` where addtime > $timestart and addtime < $timeindex and status != 4 group by userid";
		$data = Db::query($sql);
		$count = count($data);

		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 优惠券配置
	 */
	public function redconfmodify(){
		$num 		= isset($_REQUEST['num']) ? trim($_REQUEST['num']) : '';
		$amnotice 	= isset($_REQUEST['amnotice']) ? trim($_REQUEST['amnotice']) : '';
		$pmnotice 	= isset($_REQUEST['pmnotice']) ? trim($_REQUEST['pmnotice']) : '';
		$redcancel 	= isset($_REQUEST['redcancel']) ? trim($_REQUEST['redcancel']) : '';

		if (empty($num)) {
			//为0不生成优惠券
		}
		if (empty($amnotice)) {
			return ['code'=>100,'msg'=>'amnotice is empty'];
		}else{
			$a = explode("~", $amnotice);
			$am_s = $a[0];
			$am_e = $a[1];
			if (empty($am_s) || empty($am_e)) {
				return ['code'=>100,'msg'=>'amnotice is error'];
			}
		}
		if (empty($pmnotice)) {
			return ['code'=>100,'msg'=>'pmnotice is empty'];
		}else{
			$a = explode("~", $pmnotice);
			$pm_s = $a[0];
			$pm_e = $a[1];
			if (empty($pm_s) || empty($pm_e)) {
				return ['code'=>100,'msg'=>'pmnotice is error'];
			}
		}
		if (empty($redcancel)) {
			return ['code'=>100,'msg'=>'redcancel is empty'];
		}

		$table 	= "basesetting";
		$name 	= "redconf";
		$data 	= ['num'=>$num, 'am_s'=> $am_s,'am_e'=> $am_e,'pm_s'=>$pm_s,'pm_e'=>$pm_e,"redcancel"=>$redcancel];
		$up = array('content'=> json_encode($data));

		$d = Db::query("select * from `{$table}` where name=?",[$name]);
		if (!empty($d[0])) {
			Db::table($table)->where("name = '{$name}'")->update($up);
		}else{
			$up['name'] = $name;
			Db::table($table)->insert($up);
		}
		return ['code'=>200,'msg'=>'保存成功'];
	}

	/**
	 * 全局参数设置
	 */
	public function parammodify(){
		$data = isset($_REQUEST['list']) ?trim($_REQUEST['list']) : '';
		if (empty($data)) {
			return ['code'=>100,'msg'=>'data empty'];
		}
		$list = json_decode($data, true);
		if (empty($list)) {
			return ['code'=>100,'msg'=>'参数为空，无法保存'];
		}

		$a = [];
		foreach ($list as $v) {
			$a[] = $v;
		}
		$table 	= "basesetting";
		$name 	= "baseconf";
		$up = array('content'=> json_encode($a));

		$d = Db::query("select * from `{$table}` where name=?",[$name]);
		if (!empty($d[0])) {
			Db::table($table)->where("name = '{$name}'")->update($up);
		}else{
			$up['name'] = $name;
			Db::table($table)->insert($up);
		}
		return ['code'=>200,'msg'=>'保存成功'];
	}

	/**
	 * 获取基本配置参数
	 * @return [type] [description]
	 */
	public function getbaseconf(){
		$table 	= "basesetting";
		$name 	= "baseconf";

		$info 	= Db::query("select * from `{$table}` where name=?",[$name]);
		$info 	= empty($info[0])?'':$info[0]['content'];
        $array 	= [];
        if (empty($info)) {
        	//不处理
        }else{
            $a = json_decode($info,true);
            foreach ($a as $v) {
            	$array[$v['k']] = $v['v'];
            }

        }
		return $array;
	}

	/**
	 * 幻灯片列表
	 */
	public function imagelist(){
		$sql = "select * from `viewpager` where 1 order by id desc";
		$data = Db::query($sql);
		$count = count($data);

		foreach ($data as &$v) {
			$v['addtime'] 	= empty($v['addtime'])?'':date('Y-m-d H:i:s',$v['addtime']);
		}
		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 获取可用的幻灯片
	 */
	public function getviewpager(){
		$site = config('my_site_url');
		$sql = "select title,path,url from `viewpager` where status = 2 and is_delete = 0";
		$data = Db::query($sql);
		$count = count($data);

		foreach ($data as &$v) {
			$v['path'] 	= $site . $v['path'];
		}
		unset($v);
		$r = array('code' => 200, 'msg'=>'success', 'count' => $count,'data'=> $data);//200代表正常
		return $r;
	}

	/**
	 * 幻灯片上传
	 */
	public function imageadd(){
		// 允许上传的图片后缀
		$allowedExts = array("jpg", "png", "jpeg");
		$temp = explode(".", $_FILES["file"]["name"]);
		$extension = strtolower(end($temp));        // 获取文件后缀名
		$max = 2;
		if ($_FILES["file"]["size"] >= $max * 1024 * 1024){
			return array('code'=>"100","msg"=>"图片过大了");
		}
		if (!in_array($extension, $allowedExts)){
			return array('code'=>"101","msg"=>"此图片格式不支持");
		}
		if ($_FILES["file"]["error"] > 0){
			return array('code'=>"102","msg"=>"上传图片出错了");
		}

		$attachpath 	= PUBLIC_PATH ;//附件地址(本地)
		$cenpath 		= 'attachment/image/upload/' . date("Ym") . "/";
		$filename 		= time() . rand(100,999) . "." . $extension;//文件新名称
		$realpath 		= $attachpath . $cenpath .  $filename;//本地保存路径
		$savepath 		= $cenpath . $filename;//数据库保存路径
		$floderpath 	= $attachpath . $cenpath;
		if (file_exists($realpath)){
			return array('code'=>"101","msg"=>"文件已经存在");
		}else{
			if(!file_exists($floderpath)) {
				// echo $floderpath;
				if(mkdir($floderpath,0777, true)) {
					chmod($floderpath,0777);
				}else{
					return array('code'=>"100","msg"=>"创建文件夹出错");

				}
			} else {
				//echo "该文件夹已存在";
			}
			// 将文件上传到 upload 目录下
			$r = move_uploaded_file($_FILES["file"]["tmp_name"], $realpath);
			if (!$r) {
				return array('code'=>"100","msg"=>"保存文件时出错");
			}
		}
		return ['code'=>200,'msg'=>'success','path'=> '/' . $savepath];
	}

	/**
	 * 保存幻灯片
	 */
	public function imagesave(){
		$title 	= isset($_REQUEST['title']) ?trim($_REQUEST['title']) : '';
		$url 	= isset($_REQUEST['url']) ?trim($_REQUEST['url']) : '';
		$path 	= isset($_REQUEST['path']) ?trim($_REQUEST['path']) : '';
		if (empty($title) || empty($path)) {
			return ['code'=>100,'msg'=>'data empty'];
		}

		$now = time();
		$table 	= "viewpager";
		$data = ['title'=>$title,'path'=>$path,'url'=>$url,"addtime"=>$now];

		Db::table($table)->insert($data);
		return ['code'=>200,'msg'=>'保存成功'];
	}

	/**
	 * 更改幻灯片状态
	 */
	public function imagestatus(){
		$id 	= isset($_REQUEST['id']) ?intval(trim($_REQUEST['id'])) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}

		$r = Db::table('viewpager')->where("id = '{$id}'")->select();
		$status = $r[0]['status'];
		$newstatus = $status == 1?2:1;
		Db::table('viewpager')->where("id = '{$id}'")->update(['status'=>$newstatus]);
		return ['code'=>200,'msg'=>'更改状态成功'];
	}

	/**
	 * 编辑幻灯片
	 */
	public function imageedit(){
		$id 	= isset($_REQUEST['id']) ?trim($_REQUEST['id']) : '';
		$title 	= isset($_REQUEST['title']) ?trim($_REQUEST['title']) : '';
		$url 	= isset($_REQUEST['url']) ?trim($_REQUEST['url']) : '';
		$path 	= isset($_REQUEST['path']) ?trim($_REQUEST['path']) : '';
		if (empty($id)) {
			return ['code'=>100,'msg'=>'id empty'];
		}
		if (empty($title) || empty($path)) {
			return ['code'=>100,'msg'=>'data empty'];
		}

		$now = time();
		$table 	= "viewpager";
		$data = ['title'=>$title,'path'=>$path,'url'=>$url];

		Db::table($table)->where("id = '{$id}'")->update($data);
		return ['code'=>200,'msg'=>'保存成功'];
	}
}

