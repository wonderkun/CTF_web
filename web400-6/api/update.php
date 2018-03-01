<?php
require('../class/header.php');

if(!isset($_SESSION['user'])){
	exit("This is not where you should come, Hacker!!!");	
}
$user = $_SESSION['user'];

function GetIP(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif(!empty($_SERVER["REMOTE_ADDR"])){
		$cip = $_SERVER["REMOTE_ADDR"];
	}
	else{
		$cip = "NULL";
	}
	 return $cip;
}

if(!empty($_POST['message']) && !empty($_POST['email']) && !empty($_POST['csrftoken'])){

	$message = trim($_POST['message']);
	$email = trim($_POST['email']);
	$csrftoken = trim($_POST['csrftoken']);

	if(!get_magic_quotes_gpc()) { 
        $message = addslashes($message);
        $email = addslashes($email);
        $csrftoken = addslashes($csrftoken);
	} 

	if(!empty($_SESSION['csrftoken'])){
		$real_ct = $_SESSION['csrftoken'];
	}		
	if($real_ct != $csrftoken){
		exit("bad guys, what are you doing?");
	}

	$file  = 'it51zlog_update23.log';
	$content = sprintf("ip: %s , user: %s \r\nemail: %s , message: %s\r\n-----------\r\n", GetIP(), $user, $email, $message);
	$f  = file_put_contents($file, $content,FILE_APPEND);


	$query = "UPDATE `users` SET `email` = '{$email}',`message` = '{$message}' WHERE `username` = '{$user}'";
	$result = $db->query($query);

	if($result){
		exit("update success");
	}else{
		exit("Something error....");
	}

}else{
	exit("This is not where you should come, Hacker!!!");
}