<?php
namespace helper;
class Db
{
	private $conn;//数据库连接标识
	private static $client;//类的实例
	//私有构造函数，禁止外界new
	private function __construct()
	{	
		ini_set('display_errors','On');
		error_reporting(E_ALL);
		$dbconfig = \config\Config::$db;
		$dsn= $dbconfig['dsn'];
		$user = $dbconfig['user'];
		$pass = $dbconfig['password'];
		try {
			$this->conn = new \PDO($dsn,$user,$pass);

			$this->conn->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
		} catch (\Exception $e) {
			exit($e->getMessage());	
		}
	}

	//静态方法，提供类的实例
	public static function client(){
		if(!self::$client){
			self::$client = new Db();
		}
		return self::$client;
	}

	//查询
	public function query($sql){
		$data = array();
		$res = $this->conn->query($sql);
		foreach ($res as $row) {
			$data[] = $row;
		}
		return $data;
	}

	//插入，更新
	public function execute($sql){

		try {
		var_dump($sql);
			print_r($this->conn->exec($sql));
		}
		catch (PDOException $e){
			print_r($e->getMessage());
			die;
		}	

	}

}
