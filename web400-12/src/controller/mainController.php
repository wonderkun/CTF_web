<?php
class MainController extends BaseController {

	function actionIndex(){
		
		if(isset($_SESSION["data"])){
			$session = unserialize($_SESSION["data"],["allowed_classes" => ["Session"]]);
			$ip = arg("REMOTE_ADDR");
			$userAgent = arg("HTTP_USER_AGENT");
			$this->now = $session::getTime(time());
			if($session->isAccountSec($ip,$userAgent)){
				$userinfo = $session->getUserInfo();
				$this->username =  $_SESSION['username'];
				$this->loginTime = $userinfo[1];
				$userId = $userinfo[0];
				$user = new User();
				$res = $user->query("SELECT picture FROM `{$user->table_name}` where `id`='{$userId}'");
				if(!empty($res)){
					$this->picSrc = $res[0]['picture'];
				}else{
					$this->picSrc = "/img/pic.jpg";
				}
			}else{
				echo "<script>alert('your cookie my be stealed by hacker!');</script>";
				session_destroy();
				$this->jump("/main/login");
			}	
		}else{
			$this->jump("/main/login");
			return ;
		}
	}

	// 接收提交表单
	function actionLoginOut(){
		session_destroy();
		$this->jump("/main/login");
	}
	
	function actionLogin(){
		
		if($_POST){
			$username = arg('username');
			$password = arg('password');
			$ip = arg('REMOTE_ADDR');
			$userAgent = arg('HTTP_USER_AGENT');
            if (empty($username) || empty($password)) {
                echo "<script>alert('Username or password is empty.')</script>";
			}else{
				$user = New User();
				$password = md5($password);
				$res = $user->query("SELECT * FROM `$user->table_name` where `username`='{$username}' AND `password`='{$password}'");
				if(empty($res) || $res[0]['password']!==$password){
					echo "<script>alert('Username or password is error.')</script>";
				}else{
					$session = new Session($res[0]["id"],time(),$ip,$userAgent);
					$_SESSION['data'] = serialize($session);
					$_SESSION['username'] = $username;
					$this->jump("/main/index");
				}
			}
		}
	}
	
	function actionRegister(){

		if($_POST){
			$username = arg('username');
			$password = arg('password');
			if(empty($username)||empty($password)){
				echo "<script>alert('Username or password is error.')</script>";
		}else{
			$password = md5($password);
			$user = New User();
			$res = $user->query("SELECT * FROM `{$user->table_name}` WHERE `username` ='{$username}'");
			if(!empty($res)){
				echo "<script>alert('Username is registered!.')</script>";
			}else{
				$res = $user->create([
				"username"=>$username,
				"password"=>$password,
				"picture"=>"/img/pic.jpg"]);
				if(!$res) echo "<script>alert('something error. register fiaied!')</script>";
				else $this->jump("/main/login");
			}
		}
		
		}
	}

	private function randomStr($len=32){
		$baseStr = "abcdefghijklmnopqrstuvwxyz0123456789";
		$randStr = "";
		for($i=0;$i<$len;$i++){
			$randStr.= $baseStr[mt_rand(0,strlen($baseStr)-1)];
		}
		return $randStr;
	}

    public function actionUpload(){
		if(!isset($_SESSION["data"])) {
			$this->jump("/main/login");
			return;
		}
		$session = unserialize($_SESSION["data"],["allowed_classes" => ["Session"]]);
		$ip = arg("REMOTE_ADDR");
		$userAgent = arg("HTTP_USER_AGENT");
		if(!$session->isAccountSec($ip,$userAgent)){
			echo "<script>alert('your cookie my be stealed by hacker!');</script>";
			session_destroy();
			$this->jump("/main/login");
			return;
		}
		$userId = $session->getUserInfo()[0];
		// var_dump($_FILES);
		// return ;
		if(empty($_FILES['upfile'])){
		   echo '<script>alert("Upload file empty!")</script>';
		   $this->jump("/main/index");
		   return ; 
		}
		if($_FILES["upfile"]['error'] != UPLOAD_ERR_OK) {
				echo '<script>alert("upload file error!")</script>';
				$this->jump("/main/index");
				return;
			}
		if($_FILES["upfile"]['size'] > 102400) {
			echo '<script>alert("upload file too big!")</script>';
			$this->jump("/main/index");
			return;
		}
		
		$fileName = $_FILES['upfile']['name'];
		$fileExt = isset(pathinfo($fileName)['extension'])?pathinfo($fileName)['extension']:"png";
		$fileExt = addslashes($fileExt);
		$filename = $this->randomStr().'.'.$fileExt;	
		$realFileName = APP_DIR.DS."img".DS."upload".DS.$filename;
		
		if(move_uploaded_file($_FILES['upfile']['tmp_name'],$realFileName)){
			$user = New User();
			$webFileName = DS."img".DS."upload".DS.$filename;
			$res = $user->execute("UPDATE `{$user->table_name}` set `picture`='{$webFileName}' where `id`='{$userId}'");
			if($res){
				echo '<script>alert("Upload file success!")</script>';
			}else{
                echo '<script>alert("Upload file error!")</script>';
			}
			$this->jump("/main/index");
			return;
		}else{
			echo '<script>alert("Upload file Error!")</script>';
			$this->jump("/main/index");
			return ;
		}
	}
	public function actionMessage(){
		if(!isset($_SESSION['data'])){
			$this->jump("/main/login");
			return;			
		}
		$session = unserialize($_SESSION["data"],["allowed_classes" => ["Session"]]);
		$ip = arg("REMOTE_ADDR");
		$userAgent = arg("HTTP_USER_AGENT");
		$this->now = $session::getTime(time());
		if(!$session->isAccountSec($ip,$userAgent)){
			echo "<script>alert('your cookie my be stealed by hacker!');</script>";
			session_destroy();
			$this->jump("/main/login");
			return ;
		}
		$messages = array();
		$message = New Message();
		$user = New User();		
		$res = $message->query("SELECT * FROM `{$message->table_name}` order by `id` desc  limit 0,100");
		foreach($res as $key =>$value){
			 $id = $value["userid"];
			 $userinfo = $user->query("SELECT * FROM `{$user->table_name}` WHERE `id`='{$id}'");
			 if(!empty($userinfo)){
				 $username = $userinfo[0]["username"];
				 $picture = $userinfo[0]["picture"];
				 array_push($messages,array(
					 "picture"=>$picture,
					 "username"=>$username,
					 "message" =>$value["content"]
				 ));
			 }
		}
		$this->messages = $messages;
	}
	public function actionPost(){
		if(!isset($_SESSION["data"])) {
			$this->jump("/main/login");
			return;
		}
		$session = unserialize($_SESSION["data"],["allowed_classes" => ["Session"]]);
		$ip = arg("REMOTE_ADDR");
		$userAgent = arg("HTTP_USER_AGENT");
		$this->now = $session::getTime(time());

		if(!$session->isAccountSec($ip,$userAgent)){
			echo "<script>alert('your cookie my be stealed by hacker!');</script>";
			session_destroy();
			$this->jump("/main/login");
			return ;
		}
		$userId = $session->getUserInfo()[0];

		if($_POST){
           $msg= arg("msg");
		   $message = new Message();
		   $res = $message->create([
			   "userid"=>$userId,
			   "content"=>$msg
		   ]);
		   $this->jump("/main/Message");
		}
	}
}