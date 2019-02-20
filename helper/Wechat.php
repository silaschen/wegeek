<?php 
namespace helper;
/**
* wechat,
*include many static function you may often use when developing
*/

class Wechat
{
	
	public $accesstoken;
	public $appid;
	public $secret;
	public $token;

	public function __construct(){
		$this->appid = \config\Config::$wechat['APPID'];
		$this->secret = \config\Config::$wechat['SECRET'];
		$this->getAccessToken();
	}


	public function getAccessToken(){

		$key = self::GetKeyToken();
		$redis  = redisClient::client();
		print_r($this->token);
		if($redis->get($key)){

			$this->token = $redis->get($key);

		}else{
			
			$url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",$this->appid,$this->secret);
			$info = Assist::CallServer($url);
	
			$token = json_decode($info['content'],true);
			$this->token = $token['access_token'];
			$redis->set($key,$this->token,$token['expires_in']);

		}

	}

	public static function GetKeyToken(){
		
		return sprintf("AccessKey-%s",'mini');

	}

	
	public function tplmsg($data){

		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s",$this->token);
		$ret = json_decode(Assist::CallServer($url,'POST',[],$data)['content'],true);
		if($ret['errcode'] != 0 ){
			return $ret;
		}

		return true;

	}


	public function getSecData($sessionkey,$iv,$encryptedData){
		
		$pc = new \werun\WXBizDataCrypt($this->appid, $sessionkey);
		$errCode = $pc->decryptData($encryptedData, $iv, $data );

		if ($errCode == 0) {
   			 $run = json_decode($data,true);

        		return json_encode($run);

		} else {
    			return $errCode;
		}

	}



	
}
