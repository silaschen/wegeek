<?php 
namespace helper;
/**
*redis operate class
*author:silaschen
*wechat:talentchensw
*redis 单例模式
*/
class redisClient
{
	public static $client;
	private $redis;
	private function __clone(){} 

	private function __construct()
	{
		$rediscfg = \config\Config::$redis;
		$this->redis = new \Redis();
		$this->redis->connect($rediscfg['host'],$rediscfg['port']);
		if(isset($rediscfg['db'])){
			$this->redis->select($rediscfg['db']);
		}
	}

	/**
	* static function provide client of class
	*anywhere to get instance using \helper\redisClient::client()
	*/
	public static function client(){
		if(!self::$client){
			self::$client = new self();
		}
		return self::$client;
	}

	/*
	*get the value
	*/
	public function get($key){
		return $this->callAction('get',array($key));
	}



	public function set($key,$value,$time=NULL){
		if($time>0){
			$this->callAction('setex',array($key,$time,$value));
		}else{
			$this->callAction('set',array($key,$value));
		}
	}
	/**
	*this is a express way to use redis func
	*for example:callAction
	*/
    public function callAction($name, $arguments=array()) {
        return call_user_func_array(array($this->redis, $name), $arguments);
    }

}