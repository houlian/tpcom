<?php
namespace app\wapp\action;

use think\Model;
use app\index\model\MemberModel;
use app\wapp\action\CurlAction;
use app\wapp\action\MemberAction;
use app\wapp\action\OrderAction;
use app\wapp\action\RedAction;
use think\Db;

/**
 * 微信connection接口类
 * 处理与微信和小程序的相关接口
 */
class WappAction extends Model{
	private static $wapp_api = 'https://api.weixin.qq.com/sns/jscode2session?';//appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code//小程序登陆接口
	private static $appid = 'wx1c664ced54254b8d';//小程序appid
	private static $secret = '673c36a3ad82fa2e828078124d5de7e8';//小程序screct
	private static $grant_type = 'authorization_code';//固定值
	private static $wapp_prepay_api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//小程序预付接口
	private static $wapp_orderquery_api = 'https://api.mch.weixin.qq.com/pay/orderquery';//小程序预付接口
	private static $wapp_company_pay_api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';//微信商户支付api
	private static $wapp_gettransferinfo_api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';//查询企业付款api
	private static $company_number = '1513229281';//'1503928391';//商户号
	private static $paynotice = '宅点点-购买积分';
	private static $notify_url = 'https://jdpt.wshoto.com/wapp/index/depositnotify';
	private static $company_key = 'jiangnan2018jiangnan2018jiangnan';//商户平台设置的密钥key

	public function getAppid()
	{
		return self::$appid;
	}

	public function checkLogin(){
		$code 	= isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		// echo $code;
		if(empty($code)){
			return ['code'=>100,'msg'=>'code empty'];
		}

		$wapp_url = self::$wapp_api;
		$wapp_url .= 'appid=' . self::$appid;
		$wapp_url .= '&secret=' . self::$secret;
		$wapp_url .= '&js_code=' . $code;
		$wapp_url .= '&grant_type=' . self::$grant_type;
		// echo $wapp_url;

		$curl 	= new CurlAction();
		$r 		= $curl->curl_get($wapp_url);
		$data 	= json_decode($r,true);
		if (isset($_REQUEST['debug'])) {
			// echo $wapp_url;
			// // return [];
			// var_dump($r);
		}
		if(empty($data)){
			return ['code'=>101,'msg'=>'code get openid error ' . $r];
		}

		if (empty($data['openid'])) {
			return ['code'=>102,'msg'=>'code get openid empty'];
		}

		 //添加（更新）用户
		$user = (new MemberAction())->wappLogin($data['openid'],$data['session_key']);
		//地址数据

		$data = [];

		//园区地址配置
		// $address = db('basesetting')->where("`name` = 'address'")->limit(1)->select();
  //       $address = $address[0]['content'];
  //       $data['address'] = json_decode($address,true);
        //是否有优惠券
        $hasred 	= (new RedAction())->hasred($user['id']);
        $user 		= array_merge($user, $hasred);
		$data['user'] = $user;
		//配置信息
		$SettingAction = new \app\index\action\SettingAction();
		$conf = $SettingAction->getbaseconf();
		$data['conf'] = $conf;
		//园区地址配置
        $data['addressArray'] = $SettingAction->getAddressJson();

        return ['code'=>200,'msg'=>'get openid success', 'data'=>$data];//200代表正常返回
	}

	/**
	 * 充值逻辑处理
	 * @return [type]
	 */
	public function deposit(){
		$userid 	= isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';
		$openid 	= isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
		$pay 		= isset($_REQUEST['pay']) ? round(floatval(trim($_REQUEST['pay'])),2) : '';

		$now = time();
		if(empty($userid)){
			return ['code'=>100,'msg'=>'userid empty'];
		}
		if(empty($openid)){
			return ['code'=>100,'msg'=>'openid empty'];
		}
		if(empty($pay)){
			return ['code'=>100,'msg'=>'pay empty'];
		}
		// elseif ($pay % 5 != 0) {//需是五的倍数
		// 	return ['code'=>100,'msg'=>'充值金额需要是5的倍数'];
		// }

		$pay = $pay * 100;//保存扩大100倍的数值(分)
		//先插入订单记录
		$array = ['userid'=>$userid, 'openid'=>$openid,'pay'=>$pay,'addtime'=>$now];
		$recordid = (new OrderAction())->addDepositRecord($array);//充值记录id
		if (!$recordid) {
			return ['code'=>101,'msg'=>'insert database error'];
		}

		$nonce_str 	= self::createRandNumber(16);
		$localip	= self::getlocalip();
		$wapp_prepay_api = self::$wapp_prepay_api;

		$params = [];
		$params['appid'] 			= self::$appid;
		$params['mch_id'] 			= self::$company_number;
		$params['nonce_str'] 		= $nonce_str;

		$params['body'] 			= self::$paynotice;
		$params['out_trade_no'] 	= $recordid;
		$params['total_fee'] 		= $pay;
		$params['spbill_create_ip'] = $localip;
		$params['notify_url'] 		= self::$notify_url;
		$params['trade_type'] 		= 'JSAPI';
		$params['openid'] 			= $openid;

		$sign = self::createSign($params);//生成签名
		$params['sign'] = $sign;
		$xml = $this->ToXml($params);//参数数组转xml

		$curl 	= new CurlAction();

		// echo $wapp_prepay_api . '<br>';
		// echo $xml;
		// return ;
		$result = $curl->postXmlCurl($wapp_prepay_api, $xml, false, 30);//不使用证书
		$result = $this->FromXml($result);

		if(!($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS')){//调用不成功
			//记录日志

			return ['code'=>108,'msg'=>'request weixin api failed'];
		}

		$prepay_id = $result['prepay_id'];

		$data= [];
		$data['appId']		= self::$appid;
		$data['timeStamp'] 	= strval(time());
		$data['nonceStr'] 	= self::createRandNumber();
		$data['package'] 	= 'prepay_id=' . $prepay_id;
		$data['signType']	= 'MD5';

		$paySign = self::createSign($data);//再次生成签名
		$data['paySign'] = $paySign;
		unset($data['appId']);

		return ['code'=>200,'msg'=>'success','data'=>$data];

	}

	/**
	 * 充值通知处理
	 */
	public function depositnotify(){
		// trace("-----------------" . $GLOBALS['HTTP_RAW_POST_DATA'],'info');
		if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {//$postdata = file_get_contents("php://input");
			# 如果没有数据，直接返回失败
			return ['return_code' => 'FAIL','return_msg' => 'not iput'];
		}
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$xml = $this->FromXml($xml);

		$return_code 	= $xml['return_code'];
		$return_msg 	= empty($xml['return_msg'])?'':$xml['return_msg'];
		if ($return_code != 'SUCCESS') {
			//记录日志
			return ['return_code' => 'FAIL','return_msg' => 'not success'];
		}

		$bool = $this->checkSign($xml);
		if (!$bool) {
			// return ['return_code' => 'FAIL','return_msg' => 'error signature'];
		}

		//业务处理
		$result_code 	= $xml['result_code'];		//交易标识
		$total_fee 		= $xml['total_fee'];//金额 单位分
		$transaction_id = $xml['transaction_id'];//微信支付订单号
		$out_trade_no 	= $xml['out_trade_no'];//商户系统内部订单号
		$time_end 		= $xml['time_end'];//支付完成时间

		$table = 'depositrecord';
		if($result_code == 'SUCCESS'){
			//支付成功
			//开启事务
			Db::startTrans();
			$rollback = false;
			$msg = "update database error";
			try{
				$status 	= 2;
				$note 		= $total_fee;
				$up 		= ['status' => $status, 'transaction_id' => $transaction_id, 'endtime'=>$time_end, 'note'=>$note];
				$r 			= Db::table($table)->where('id', $out_trade_no)->update($up);
				if ($r) {
					//用户更新积分余额
					$r2 = (new MemberAction())->pointplus($out_trade_no);
					if (!$r2) {
						$rollback = true;
					}
				}else{
					$rollback = true;
				}
				

			}catch(\Exception $e){
				$rollback = true;
				$msg = $e->getMessage();
			}
			if ($rollback !== false) {
			    Db::rollback();
			    return ['return_code' => 'FAIL','return_msg' => $msg];
			}
		    Db::commit();
			return ['return_code' => 'SUCCESS'];//支付成功

		}
		//支付失败
		$status 	= 3;
		$note 		= $total_fee;
		$up 		= ['status' => $status, 'transaction_id' => $transaction_id, 'endtime'=>$time_end, 'note'=>$note];
		$r = Db::table($table)->where('id', $out_trade_no)->update($up);
		if ($r) {
			return ['return_code' => 'SUCCESS'];
		}
		return ['return_code' => 'FAIL','return_msg' => 'update database error'];
	}


	/**
	 * 查询支付订单
	 * @param [type] $[transaction_id]  微信的订单号，优先使用
	 * @param [type] $[out_trade_no] 商户系统内部订单号 二选一
	 */
	public function orderquery($transaction_id = '',$out_trade_no = ''){
		$nonce_str 	= self::createRandNumber(16);
		$api 		= self::$wapp_orderquery_api;

		$params = [];
		$params['appid'] 			= self::$appid;
		$params['mch_id'] 			= self::$company_number;
		$params['nonce_str'] 		= $nonce_str;

		$params['transaction_id'] 	= $transaction_id;
		// $params['out_trade_no'] 	= $out_trade_no;

		$sign = self::createSign($params);//生成签名
		$params['sign'] = $sign;
		$xml = $this->ToXml($params);//参数数组转xml

		$curl 	= new CurlAction();

		// echo $api . '<br>';
		// echo $xml;
		// return ;
		$result = $curl->postXmlCurl($api, $xml, false, 30);//不使用证书
		$result = $this->FromXml($result);
		print_r($result);
		//校验返回数据
		if ($result['return_code'] == 'SUCCESS') {//通信正常
			if ($result['result_code'] == 'SUCCESS') {//查询成功
				// echo "查询成功";
				if ($result['trade_state'] == 'SUCCESS') {//支付成功
					# code...
				}elseif (false) {
					# code...
				}
			}
		}
	}


	/**
	 * 微信商户支付
	 * @param [type] $[openid] [<description>]
	 * @param [type] $[pay] [<金额>]
	 * @param [type] $[recordid] [订单id]
	 */
	public function wappwithdraw($openid, $pay, $recordid){
		$nonce_str 	= self::createRandNumber(16);
		$localip	= self::getlocalip();
		$api = self::$wapp_company_pay_api;

		$params = [];
		$params['mch_appid'] 		= self::$appid;
		$params['mchid'] 			= self::$company_number;
		$params['nonce_str'] 		= $nonce_str;

		$params['partner_trade_no'] = $recordid;
		$params['openid'] 			= $openid;
		$params['check_name'] 		= 'NO_CHECK';//不校验真实用户名
		$params['amount'] 			= $pay;//金额(单位分)
		$params['desc'] 			= '退款';
		$params['spbill_create_ip'] = $localip;

		$sign = self::createSign($params);//生成签名
		$params['sign'] = $sign;
		$xml = $this->ToXml($params);//参数数组转xml

		$curl 	= new CurlAction();

		// echo $api . '<br>';
		// echo $xml;
		// return ;
		$result = $curl->postXmlCurl($api, $xml, true, 30);//使用证书
		$result = $this->FromXml($result);
		// print_r($result);
		//校验返回数据
		if ($result['return_code'] == 'SUCCESS') {//通信正常
			if ($result['result_code'] == 'SUCCESS') {//支付成功
				return ['code' => 200, "msg" => "success","data" => $result];
			}
		}
		return $result;
	}

	/**
	 * 查询企业付款
	 */
	public function gettransferinfo($partner_trade_no){
		$nonce_str 	= self::createRandNumber(16);
		$api 		= self::$wapp_gettransferinfo_api;

		$params = [];
		$params['appid'] 			= self::$appid;
		$params['mch_id'] 			= self::$company_number;
		$params['nonce_str'] 		= $nonce_str;
		$params['partner_trade_no'] = $partner_trade_no;

		$sign = self::createSign($params);//生成签名
		$params['sign'] = $sign;
		$xml = $this->ToXml($params);//参数数组转xml

		$curl 	= new CurlAction();

		// echo $api . '<br>';
		// echo $xml;
		// return ;
		$result = $curl->postXmlCurl($api, $xml, true, 30);//不使用证书
		$result = $this->FromXml($result);
		print_r($result);
		//校验返回数据
		if ($result['return_code'] == 'SUCCESS') {//通信正常
			if ($result['result_code'] == 'SUCCESS') {//查询成功
				// echo "查询成功";
				if ($result['status'] == 'SUCCESS') {//支付成功
					# code...
				}elseif (false) {
					# code...
				}
			}
		}
	}

	/**
	 * 生成指定长度随机字符串
	 * @param  数字
	 * @return [string]
	 */
	public static function createRandNumber($length = 16){
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		} 
		return $str;
	}

	/**
	 * 生成微信支付参数md5签名
	 */
	public static function createSign($array){
		//签名步骤一按照键名字典排序 生成URL格式的字符串
		ksort($array);   
		$str = self::toUrlParams($array);
		//签名步骤二：在string后加入KEY
		$str = $str . "&key=".self::$company_key;
		//签名步骤三：MD5加密+签名步骤四：所有字符转为大写
		$str = strtoupper(md5($str));
		return $str;
	}

	/**
	 * 格式化参数格式化成url参数
	 */
	public static function toUrlParams($array){
		$buff = "";
		foreach ($array as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}


	/**
	 * 输出xml字符
	 * @throws WxPayException
	**/
	public function ToXml($array)
	{
		if(!is_array($array) || count($array) <= 0)
		{
    		throw exception('数组数据异常', 100006);
    	}
    	
    	$xml = "<xml>";
    	foreach ($array as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	public function FromXml($xml)
	{	
		if(!$xml){
			
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $array;
	}


	/**
	 * 签名验证
	 */
	public function checkSign($xml){
		if(empty($xml['sign'])){
			// exception('签名错误', 100006);
			return false;
		}
		
		$sign = self::createSign($xml);//生成签名
		if($xml['sign'] == $sign){
			//签名正确
			return true;
		}
		// exception('签名错误', 100007);
		return false;
	}

	/**
	* 判断签名，详见签名生成算法是否存在
	* @return true 或 false
	**/
	public function IsSignSet($xml)
	{
		return array_key_exists('sign', $xml);
	}

	/**
	 * 获取客户端idp
	 */
	public static function getip() {
		static $ip = '';
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
	}

	//获取本地ip
	public static function getlocalip(){
		return gethostbyname($_SERVER['HTTP_HOST']);
	}
}

