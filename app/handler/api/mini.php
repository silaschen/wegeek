<?php
namespace app\handler\api;
use helper\redisClient;
use helper\Assist;
use helper\Wechat;
use helper\FileUpload;
function executeRequest(){
	$handler = new MiniHandler();
	$handler->run();
}


define('APPID', 'wx8482a902f90eb22d');
define('SECRET', 'f01ba4f4e758363a1d128fd12c79f67a');
class MiniHandler{
	public $openid;
	
	public function run(){
			ini_set('date.timezone','Asia/Shanghai');

		$action = filter_input(INPUT_GET, 'action');
		switch ($action) {
			case 'login':
				$this->OnLogin();
				break;
			case 'slide':
				$this->Slide();
				break;
			case "deal":
				$this->deal();
				break;
			case "token":
				$this->info();
				break;
			case "db":
				$this->Db();
				break;
			case "getres":
				$this->getRes();
				break;
			case "getupload":
				$this->getupload();
				break;
		}
	}

	function __construct(){
		
		$this->wechat= new Wechat();

	}	
	

	public function getRes(){

		//print_r($_FILES);
		//$upload = new FileUpload('file');
		//$upload->upload();
		//if($upload->uploadok()){
			//$path = $upload->fileinfo['target'];
			
			//$baseimg = $this->base64Image($path);
			$baseimg=$this->base64Image($_FILES['file']['tmp_name']);
			$ret = \helper\Assist::CallAI("predict",json_encode(["image"=>$baseimg]));
			if($ret['code'] == 200){
				$retd = json_decode($ret['content'],true);
				outputjson($retd);

			}
		//}
	}

	

	public function getupload(){

		//print_r($_FILES);
		//$upload = new FileUpload('file');
		//$upload->upload();
		//if($upload->uploadok()){
			//$path = $upload->fileinfo['target'];
			
			//$baseimg = $this->base64Image($path);
			$baseimg=$this->base64Image($_FILES['file']['tmp_name']);
			$ret = \helper\Assist::CallAIhelp("predict",json_encode(["image"=>$baseimg]));
			if($ret['code'] == 200){
				$retd = json_decode($ret['content'],true);
				outputjson($retd);

			}
		//}
	}


	function base64Image ($image_file) {
  		$base64_image = '';
  		$image_info = getimagesize($image_file);
	  	$image_data = fread(fopen($image_file, 'r'), filesize($image_file));
 		// $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
		$base64_image = chunk_split(base64_encode($image_data));
	  	return $base64_image;
	}	











	public function Db(){
		$db = \helper\Db::client();
		$sql = sprintf("insert into user_info (user_name,user_pwd) VALUES ('%s','%s')",'xiao','1234');
		$db->execute($sql);

	}

	public function deal(){
			$this->IsCached();
			$appid = \config\Config::$wechat['APPID'];
			$sessionKey = $this->sessionkey;
			$da = json_decode(file_get_contents("php://input"),true);
			$Data=$da['run'];
			$iv  = urldecode($da['iv']);
			$ret =json_decode($this->wechat->getSecData($this->sessionkey,$iv,$Data),true)['stepInfoList'];
			foreach($ret as $k=>$v){

				$ret[$k]['time'] = date("Y-m-d",$v['timestamp']);
				$ret[$k]['x'] = floor(($v['step']*100)/10000);
			}
			
			$red = self::OrderData($ret,'timestamp');
			exit(json_encode($red));


	}


	public function OrderData($data,$key){
		foreach($data as $k=>$v){

			$ret[$k]=$v[$key];
		}		
		
		krsort($ret);
		//print_r($ret);die;
		$res = [];
		foreach($ret as $kk=>$vv){
			
			array_push($res,$data[$kk]);

		}

		return $res;


	}

	public function info(){
		$info  = $this->IsCached();
	
		$we = new Wechat();
		$data = json_decode(file_get_contents("php://input"),true);
			
         	$data['touser'] = $this->openid;
      		$data['template_id'] = 'O_3Cq3dLUfghuKQttLjmKyQwApc__xXw_assJCRRBJQ';
      $data['page'] = '';
      $data['form_id']=$data['formId'];
      $data['data']['keyword1']['value'] = 'test';
      $data['data']['keyword1']['color'] = '#173177';
      $data['data']['keyword2']['value'] = 'bank transfer';
      $data['data']['keyword2']['color'] = '#173177';
      $data['data']['keyword3']['value'] = '1000';
      $data['data']['keyword3']['color'] = '#173177';
      $data['data']['keyword4']['value'] =  date("Y-m-d",time());
      $data['data']['keyword4']['color'] = '#173177';

	$info = $we->tplmsg($data);
	print_r($info);
   }

	public function OnLogin(){
		$code = $_GET['code'];
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.APPID.'&secret='.SECRET.'&js_code='.$code.'&grant_type=authorization_code';
	
		$r = json_decode(file_get_contents($url),true);
		//print_r($r);die;
	
		$key = substr(md5(time().$r['openid']),8,16);
		$this->openid = $r['openid'];
		$this->sessionkey=$r['session_key'];
		$redis = redisClient::client();
		$redis->set($key,json_encode($r),9000);
		
		exit(json_encode(array('ret'=>1,'sessionkey'=>$key)));
	}



	public function WxDelName($str) {
	    if($str){
	        $tmpStr = json_encode($str);
	        $tmpStr2 = preg_replace("#(\\\ud[0-9a-f]{3})#ie","",$tmpStr);
	        $return = json_decode($tmpStr2);
	        if(!$return){
	            return jsonName($return);
	        }
	    }else{
	        $return = '微信用户-'.time();
	    }    
	    return $return;
	}


	#获取轮播图#
	public function Slide(){
		$imgs = [
				['pic'=>'http://h.hiphotos.baidu.com/image/pic/item/dcc451da81cb39dbc1f90411dd160924ab1830bf.jpg'],
			];

		//header("Content-Type:application/json");
		exit(json_encode(['imgs'=>$imgs]));
	}

	protected function IsCached($sk = false){
		$sk = $_GET['sessionkey'];
		$info = json_decode(redisClient::client()->get($sk),true);
		if(!$info) exit(json_encode(['msg'=>'error']));
		//print_r($info);
		$this->openid = $info['openid'];
		$this->sessionkey=$info['session_key'];
		if(!$info['openid']) return false;
		return $info;
	}

	#上传图片#
	public function UploadFile(){
		$r = $this->upload('images/'.date('Y-m-d'),array('ext'=>'png,jpg,gif,jpeg,mp4,avi'));
		if($r['file']){
			exit(json_encode(array('code'=>1,'file'=>$r['file'])));
		}else{
			exit(json_encode(array('code'=>0,'err'=>$r)));
		}
	}

	#返回用户信息#
	public function UserInfo(){
		$this->IsCached();
		$type = I('type');
		$user = M('user_list')->where(array('openid'=>$this->openid))->find();
		if(!$user) exit(json_encode(array('code'=>0)));
		if($type == 2){
			$car = M('car_drivelist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('DRIVE_CARD');
		}else if($type == 1 ){
			$car = M('car_xszlist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('TRAVEL_CARD');
		}else if($type == 3){
			$car = M('car_wtslist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('INSPECT_CARD');
		}else if($type == 4){
			$car = M('car_cpzlist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('REPLATE_CARD');
		}else if($type == 5){
			$car = M('car_mjblist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('EXEMPT_CARD');
		}else if($type == 6){
			$car = M('car_greenlist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('GREEN_CARD');
		}else if($type == 7){
			$car = M('car_checklist')->where(array('openid'=>$this->openid))->order('id desc')->find();
			$data['price'] = C('CHECK_CARD');
		}
		$data['car'] = $car; 
		$imgs = M('sys_advs')->where(array('type'=>1,'status'=>1))->select();
		exit(json_encode(array('code'=>1,'info'=>$data,'user'=>$user,'imgs'=>$imgs)));
	}

	#获取支付参数#
	protected function PayConf($orderid,$total,$body,$attch,$url){
		import("@.ORG.WeiXin");
        $WX = new WeiXin();
        $WX->appId = APPID;
        $WX->openid = $this->openid;
       	$payinfo = $WX->payconfig($orderid,$total*100,$body,$attch,$url);
        $r = $WX->payjsapi($payinfo['prepay_id']);
        if($payinfo['prepay_id']){
        	if($attch == 'Drive/OrderDone'){
        		M('car_drivelist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']); //用于发送模版消息提醒
        	}else if($attch == 'Check/OrderDone'){
        		M('car_checklist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}else if($attch == 'Travel/OrderDone'){
        		M('car_xszlist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}else if($attch == 'Green/OrderDone'){
        		M('car_greenlist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}else if($attch == 'RePlate/OrderDone'){
        		M('car_cpzlist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}else if($attch == 'Inspect/OrderDone'){
        		M('car_wtslist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}else if($attch == 'Exempt/OrderDone'){
        		M('car_mjblist')->where(array('orderid'=>$orderid))->setField('prepay_id',$payinfo['prepay_id']);
        	}
        }else{
        	return false;
        }
		// file_put_contents('pay.txt',$r."\r\n".json_encode($payinfo)."\r\n".json_encode($info));
		return $r;
	}

	#资料设置#
	public function SetProfile(){
		$this->IsCached();
		$user = M('user_list')->where(array('openid'=>$this->openid))->find();
		if(!$user) exit(json_encode(array('code'=>0,'msg'=>'用户信息错误')));
		$data = json_decode(file_get_contents("php://input"),true);
		$data['cphm'] = ($this->FindCph($data['cpm'])).$data['cphm'];
		$flag = M('user_list')->where(array('openid'=>$this->openid))->save($data);
		if(!$flag) exit(json_encode(array('code'=>0,'msg'=>'您没做什么更改...')));
		exit(json_encode(array('code'=>1)));
	}

	#订单统计#
  	public function CountOrder(){
  		$this->IsCached();
  		$arr = array(
  			'c1'=>M('car_xszlist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c2'=>M('car_drivelist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c3'=>M('car_wtslist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c4'=>M('car_cpzlist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c5'=>M('car_mjblist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c6'=>M('car_greenlist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			'c7'=>M('car_checklist')->where(array('openid'=>$this->openid,'status'=>array('in',array(1,2,3))))->count(),
  			);
  		exit(json_encode($arr));
  	}

  	#我的订单#
  	public function GetOrder($table){
  		$p = I('p',1);
		$status = I('status');
		$map = array();
		$map['openid'] = $this->openid;
		if($status){
			$map['status'] = $status;
		}else{
			$map['status'] = array('egt',0);
		}
		$list = M($table)->where($map)->page($p.',6')->order('id desc')->select();
		for ($i=0; $i < count($list); $i++) { 
			$list[$i]['addtime'] = date('Y-m-d H:i:s',$list[$i]['addtime']);
		}
		return $list;
  	}


    #取消订单通知#
     public function NotifyCancelOrder($info){
      $this->openid = $info['openid'];
      $data['touser'] = $this->openid;
      $data['template_id'] = '9lK2K0oWoZ0FaH_8mqFLrmWQvBIccJNPoVuk5AjAwzs';
      $data['page'] = '';
      $data['form_id'] = $info['formId'];
      $data['data']['keyword1']['value'] = $info['title'];
      $data['data']['keyword1']['color'] = '#173177';
      $data['data']['keyword2']['value'] = $info['time'];
      $data['data']['keyword2']['color'] = '#173177';
      $data['data']['keyword3']['value'] = $info['reason'];
      $data['data']['keyword3']['color'] = '#173177';
      $data['data']['keyword4']['value'] =  $info['fee'];
      $data['data']['keyword4']['color'] = '#173177';
      return $this->LittleTplMsg($data);
    }



     #发货订单通知#
     public function NotifyPostOrder($info){
      $this->openid = $info['openid'];
      $data['touser'] = $this->openid;
      $data['template_id'] = 'IopaGuwPHEm-2P0FFNLeZHKTVGlY4nLN4B-nXQtYk70';
      $data['page'] = '';
      $data['form_id'] = $info['formId'];
      $data['data']['keyword1']['value'] = $info['title'];
      $data['data']['keyword1']['color'] = '#173177';
      $data['data']['keyword2']['value'] = $info['addtime'];
      $data['data']['keyword2']['color'] = '#173177';
      $data['data']['keyword3']['value'] = $info['kd'];
      $data['data']['keyword3']['color'] = '#173177';
      $data['data']['keyword4']['value'] =  $info['posttime'];
      $data['data']['keyword4']['color'] = '#173177';
      return $this->LittleTplMsg($data);
    }


	#小程序模版消息#
	protected function LittleTplMsg($data){
		import('@.ORG.WeiXin');
		$weixin = new WeiXin(APPID,SECRET); //使用小程序的APPI 获取参数
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$weixin->accesstoken;
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl);
        $r = json_decode($tmpInfo, true);
        // file_put_contents("check11.txt", $r);
        if ($r['errmsg'] == 'ok') {
            return $r;
        }
      	return false;
	}


}
