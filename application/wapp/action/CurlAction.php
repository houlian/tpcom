<?php
namespace app\wapp\action;

/**
 * 封装curl操作
 */
class CurlAction{

	public function curl_get($url = ''){
		if (empty($url)) {
			return false;
		}
		//初始化
	    $curl = curl_init();
	    //设置抓取的url
	    curl_setopt($curl, CURLOPT_URL, $url);
	    //设置头文件的信息作为数据流输出
	    // curl_setopt($curl, CURLOPT_HEADER, 1);
	    //设置获取的信息以文件流的形式返回，而不是直接输出。
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 

	    //执行命令
	    $data = curl_exec($curl);
		if (isset($_REQUEST['debug'])) {
			echo $url;
			var_dump($data);
			var_dump(curl_error($curl));
		}
	    //关闭URL请求
	    curl_close($curl);
	    //显示获得的数据
	    // print_r($data);
	    return $data;
	}

	public function curl_post($url = '',$params = array()){
		if (empty($url)) {
			return false;
		}

		//初始化
	    $curl = curl_init();
	    //设置抓取的url
	    curl_setopt($curl, CURLOPT_URL, $url);
	    //设置头文件的信息作为数据流输出
	    // curl_setopt($curl, CURLOPT_HEADER, 1);
	    //设置获取的信息以文件流的形式返回，而不是直接输出。
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    //设置post方式提交
	    curl_setopt($curl, CURLOPT_POST, 1);
	    //设置post数据
	    $post_data = $params;
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	    //执行命令
	    $data = curl_exec($curl);
	    //关闭URL请求
	    curl_close($curl);
	    //显示获得的数据
	    // print_r($data);
	    return $data;
	}


	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $url  url
	 * @param string $xml  需要post的xml数据
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WxPayException
	 */
	public static function postXmlCurl($url, $xml, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		// $curlVersion = curl_version();

		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			//证书文件请放入服务器的非web目录下
			
			$sslCertPath = ROOT_PATH . "payment/weixinpay/apiclient_cert.pem";
			$sslKeyPath = ROOT_PATH . "payment/weixinpay/apiclient_key.pem";
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $sslCertPath);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $sslKeyPath);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			echo "curl出错，错误码:$error";
		}
	}
}