<?php 
namespace helper;
/**
* Assit is a common class,
*include many static function you may often use when developing
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Assist
{
	public static function join($path){
		if(empty($path)){
			return NULL;
		}
		$retpath='';
		for ($i=0; $i < count($path); $i++) { 
			$retpath .= $path[$i];
		}
		return $retpath;
	}
	

	public static function CallAI($path,$data){

		$url = sprintf("http://%s:%s/%s",'127.0.0.1',5000,$path);
		$ret = self::CallServer($url,'POST',array(),$data);
		return $ret;



	}
	

	public static function CallAIhelp($path,$data){

                $url = sprintf("http://%s:%s/%s",'127.0.0.1',5000,$path);
                $ret = self::CallServer($url,'POST',array(),$data);
                return $ret;
        }




	/**
	*Curl get and post server
	*/
	public static function CallServer($url,$method='GET',$header=array(),$data=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置http头信息
		curl_setopt($ch, CURLOPT_HEADER, false);//开启将输出数据流
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//	TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
		if($method === 'POST'){
			curl_setopt($ch, CURLOPT_POST, true);//发起post请求
			curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data):$data);
		}
		$content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($code === 0){
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        return array(
            'code' => $code,
            'content' => $content,
        );
	}

	/**
	*send mail
	*/
	public static function sendmail($user,$subject,$body){
		$mailcfg = \config\Config::$mailcfg;
		$mail = new PHPMailer(true);                  
		try {
		    //Server settings
		    $mail->SMTPDebug = false;                              // Enable verbose debug output
		    $mail->isSMTP();           
		    $mail->CharSet='UTF-8';                           // Set mailer to use SMTP
		    $mail->Host = $mailcfg['host'];  // Specify main and backup SMTP servers
		    $mail->SMTPAuth = true;                               // Enable SMTP authentication
		    $mail->Username = $mailcfg['mailname'];                 // SMTP username
		    $mail->Password = $mailcfg['password'];                           // SMTP password
		    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		    $mail->Port = $mailcfg['port'];                               // TCP port to connect to
		    //Recipients
		    $mail->setFrom($mailcfg['mailname'], $mailcfg['from']);
		    $mail->addAddress($user);     // Add a recipient
		    // $mail->addAddress('ellen@example.com');               // Name is optional
		    // $mail->addReplyTo('info@example.com', 'Information');
		    // $mail->addCC('cc@example.com');
		    // $mail->addBCC('bcc@example.com');
		    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		    //Content
		    $mail->isHTML(true);                                  // Set email format to HTML
		    $mail->Subject = $subject;
		    $mail->Body    = $body;
		    $mail->AltBody = $body;
		    $mail->send();
		    return true;
		} catch (Exception $e) {
		    return ['msg'=>$mail->ErrorInfo];
		}
	}

	public static function isGet(){
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}

	public static function isPost(){

		return $_SERVER['REQUEST_METHOD'] === 'POST';
	} 
        
	public static function IsMobile(){
	
		return striposs('iphone',$_SERVER['HTTP_USER_AGENT']) !== false;
	}








	
}
