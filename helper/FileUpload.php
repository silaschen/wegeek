<?php 
namespace helper;
/**
* file upload class
*/
class FileUpload
{
	public $fileinfo;//file info
	private $error;//error info
	private $formname;//form name
	private $proccess=false;
	const UPLOAD_FAILED=1001;
	const NOT_ALLOWED_TYPE=1002;
	const UPLOADFILE_NOTFOUND=1003;
	const NOT_UPLOAD_PATH=1004;
	function __construct($file)
	{
		$this->formname = $file;
		
	}

	public function upload(){
		if(!$this->uploadexist() || !$this->uoloadsuccess()){
				return false;
		}
		//check whether the filetype is ok
		if($this->checkType($this->getFileExetension($_FILES[$this->formname]['name']))){
			//ok to upload
			$this->handlerupload();
		}else{
			return false;
		}
	}

	private function handlerupload(){
		try {
			$target = $this->getTarget($_FILES[$this->formname]['name']);			
			move_uploaded_file($_FILES[$this->formname]['tmp_name'],$target);
			$this->fileinfo = array(
				 "filename" => $_FILES[$this->formname]['name'],
                 "filesize" => $_FILES[$this->formname]['size'],
                 "mimetype" => $_FILES[$this->formname]['type'],
                 "target" => $target,
			);
		} catch (\Exception $e) {
			$this->error = array(
				'code' => self::UPLOAD_FAILED,
				'message' => $e->getMessage()
			);
		}
		$this->proccess=true;
	}

	private function checkType($type){
		if(!in_array($type, \config\Config::ALLOW_UPLOAD_FILE)){
			$this->error = array(
				'code' => self::NOT_ALLOWED_TYPE,
				'message' => 'Not allowed file type'
			);
			return false;
		}
		return true;
	}


	private function getTarget($name){
		$hash = sha1($name.time());
		if(!file_exists(\config\Config::UPLOADPATH)){
			$this->error = array(
				'code'=>self::NOT_UPLOAD_PATH,
				'message'=>'不存在文件上传目录'

			);
		}
		$filehash = sprintf("/%s/%s/%s/%s",substr($hash, 0,2),substr($hash, 2,2),substr($hash, 4,2),substr($hash, 5,2));
		$fullpath = \helper\Assist::join(array(\config\Config::UPLOADPATH,$filehash));
		if(!file_exists($fullpath)){
			mkdir($fullpath, 0777, TRUE);

		}
		$filesavepath = \helper\Assist::join(array($fullpath,'/'.md5($name)));
		return $filesavepath;
	}


	private function uploadexist(){
		if(!$_FILES || !array_keys($_FILES)[0]){
			$this->error = array(
				'code' => self::UPLOADFILE_NOTFOUND,
				'message' => 'no file found'
			);
			return false;
		}
		return true;
	}


	private function uoloadsuccess(){
		if($_FILES[$this->formname]['error'] != UPLOAD_ERR_OK){
			$this->error = array(
				'code' => $_FILES[$this->forname]['error'],
				'message' => $this->getErrorMessage($_FILES[$this->formname]['error'])
			);
			return false;
		}
		return true;
	}

	public function uploadok(){
		if(!$this->error && $this->proccess === true){
			return true;
		}
		return false;
	}

	public function getError(){

		return $this->error;
	}

	private function getFileExetension($name){
		$type = substr(strrchr($name, '.'),1);
		return $type;
	}

	public function getFileInfo(){
		if($this->proccess === true && !$this->error){
			return $this->fileinfo;
		}else{
			return [];
		}
	}


}








