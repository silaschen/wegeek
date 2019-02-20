<?php 
namespace app\handler\index;
use helper\redisClient;
use helper\Assist;
function executeRequest(){
	$handler = new IndexHandler();
	$handler->run();
}

class IndexHandler{
	const TOKEN="mini";

	function run(){

		$action = filter_input(INPUT_GET, 'action');
		switch ($action) {
			case 'check':
				$this->checkSignature();
				break;
			default:
				$this->index();
				break;
		}
	}

	private function checkSignature(){
		$signature = $_GET["signature"];
	$timestamp = $_GET["timestamp"];
	$nonce = $_GET["nonce"];

	$token = "minis";
	$tmpArr = array($token, $timestamp, $nonce);
	sort($tmpArr, SORT_STRING);
	$tmpStr = implode( $tmpArr );
	$tmpStr = sha1( $tmpStr );
	//file_put_contents('/var/www/mini/xxx.txt',json_encode([$tmpStr,$signature]));
	
	if( $tmpStr == $signature ){
		//return true;
		echo $_GET['echostr'];
	}else{
		return false;
	}
}


	//处理主入口
	public function index(){
		if(!$this->checkSignature()) exit;
		$postStr = file_get_contents("php://input");
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$this->fu = (string)$postObj->FromUserName;
		$this->tu = (string)$postObj->ToUserName;
		$MsgType = (string)$postObj->MsgType;
		if($MsgType == '') exit($_GET["echostr"]); //执行验证接口
		//根据消息类型进入相应操作
		switch($MsgType){
			case 'text':
		 	$content = strtolower(trim($postObj->Content)); //转为小写字母
			$this->find($MsgType,$content);
		  break;
		  case 'image':
		  	$picurl = (string)$postObj->PicUrl;
		  	$this->find($MsgType,$picurl);
		  break;
		  case 'location':
		  	$data = array();
		  	$data['px'] = $postObj->Location_X;
		  	$data['py'] = $postObj->Location_Y;
		  	$data['Label'] = $postObj->Label;
		  	$data['Scale'] = $postObj->Scale;
		  	$this->find($MsgType,$data);
		  break;
		  case 'event':
		  	$event = (string)$postObj->Event;
			$eventKey = (string)$postObj->EventKey;
			if($event == 'subscribe'){
				$this->ScanQR($postObj);
				$eventKey = '';
			}elseif($event == 'unsubscribe'){
				$eventKey = '';
			}elseif($event == 'CLICK'){
				
			}elseif($event == 'SCAN'){
				// 扫码推广二维码
				$this->ScanQR($postObj);
			}elseif($event == 'user_get_card'){
				// 用户领取卡券
				$CardId = (string)$postObj->CardId;
				R('User/UserGotWxCard',array($this->fu,$CardId));
			}elseif($event == 'LOCATION'){

			}
			$x = (string)$postObj->Latitude; //纬度
			$y = (string)$postObj->Longitude; //经度
			$l = (string)$postObj->Precision; //精度
			if($x && $y){	//上报位置
			}
			$data = array();
		  	$data['event'] = $event;
		  	$data['value'] = $eventKey;
		  	$this->find($MsgType,$data);
		  break;
		  case 'voice':
		  	$word = (string)$postObj->Recognition; //语音识别结果
		  	$this->find('text',$word);
		  break;
		}
	}


	#自定义参数二维码扫描处理 传入消息体完整对象#
	protected function ScanQR($postObj){
		$eventKey = (string)$postObj->EventKey;
		// 先检查是否为指向模块内操作
		$arr = explode('_', str_replace('qrscene_','', $eventKey));
		if($arr[1] && $arr[2] && $arr[0] == 'RUN'){
			// 参数格式正确 跨模块执行相应操作
			R($arr[1].'/'.$arr[2],array($postObj)); //带上全部消息对象 包含二维码的值
		}
		return false;
	}


	#微信支付通知#
	public function PayCallBack(){
		$xml = file_get_contents("php://input");
        import("@.ORG.WeiXin");
        $WX = new WeiXin();
        $log = $WX->FromXml($xml);
        // 安全效验
        if(!$WX->CheckSign($log))  exit('Fail');
        // 检查有无重复通知
        $notified = M('wxpay_logs')->where(array('transaction_id'=>$log['transaction_id'],'result_code'=>'SUCCESS'))->count();
		if($notified > 0){
			// 已通知过的
			exit("<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>");
		}else{
			M('wxpay_logs')->add($log); //记录
		}
	
		if(strpos($log['attach'],'/') > 0){
			R($log['attach'],array($log));
		}
  
	}


}



