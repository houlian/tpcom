<?php

namespace app\wapp\action;

use think\Db;

class RouteAction{

	/**
	 * 骑手获取今天行程
	 */
	public function route(){
		$table 	= 'route';
		$date 	= date("Y-m-d");
		$now 	= time();
		
		$r = Db::table($table)->where("`date` = '{$date}'")->select();
		if (empty($r[0])) {
			//获取地址配置
	        $address = db('basesetting')->where("`name` = 'address'")->limit(1)->select();
        	$address = empty($address[0])?'':$address[0]['content'];
        	if (empty($address)) {
        		return ['code'=>100,'msg'=>'get address config error'];
        	}
        	$address = json_decode($address, true);

        	$array = [];
        	foreach ($address as $key => $value) {
        		foreach ($value as $k => $v) {
        			$array[] = ['a'=>$k,'r'=>$key,'s'=>1];
        		}
        	}
        	$str 	= json_encode($array,JSON_UNESCAPED_UNICODE);
        	$data 	= ['content'=>$str,'addtime'=>$now,'date'=>$date];
    		$r 		= Db::table($table)->insert($data);
    		if (empty($r)) {
    			return ['code'=>100,'msg'=>'insert database error'];
    		}
    		$route = $array;
		}else {
			$route = $r[0]['content'];
			$route = json_decode($route, true);
		}
		
		return ['code'=>200,'msg'=>'success','route'=>$route];
	}

	/**
	 * 骑手更新行程
	 */
	public function routeup(){
		$address 	= isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';//地址

		if (empty($address)) {
			return ['code'=>100,'msg'=>'address empty'];
		}

		$table 	= 'route';
		$date 	= date("Y-m-d");
		$now 	= time();
		
		$r 	= Db::table($table)->where("`date` = '{$date}'")->select();
		$id = $r[0]['id'];
		$array = $r[0]['content'];
		$array = json_decode($array, true);
		foreach ($array as &$v) {
			if ($v['a'] == $address) {
				$v['s'] = 2;
				$v['t'] = $now;
				break;
			}
		}
    	$str 	= json_encode($array,JSON_UNESCAPED_UNICODE);
    	$data 	= ['content'=>$str];

    			//开启事务
		Db::startTrans();
		$rollback = false;
		$msg = "";
		try{
	    	//更新行程
			$update = Db::table($table)->where("id = '{$id}'")->update($data);
			//更新改地址订单状态
			$start 	= strtotime(date('Y-m-d'));
			$end 	= $start + 86400;

			//更新该园区订单状态
			// $sql 	= "update `order` set `status` = 3, `finishtime` = '{$now}'  where addtime > $start and addtime < $end and `status` = 2 and `addr_two` = '{$address}'";
			// echo $sql;
			// Db::execute($sql);
		} catch (\Exception $e) {
		   $rollback = true;
		}
		if ($rollback !== false) {
		    Db::rollback();
		    return ['code'=>106,'msg'=>'update database error'];
		}
	    Db::commit();	
		return ['code'=>200,'msg'=>'success'];

	}
}

