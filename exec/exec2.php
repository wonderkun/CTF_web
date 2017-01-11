<?php 

$ip = isset($_POST['ip'])?$_POST['ip']:die();

if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i',$ip)){
    die("ip 格式错误!");
}

echo strlen($ip);

if(strlen($ip)<7||strlen($ip)>21){
    die("ip 长度错误!");
}

	// Determine OS and execute the ping command.
if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
		// Windows
		
	$cmd = shell_exec( 'ping  ' .$ip );
}else {
		// *nix
		$cmd = shell_exec( 'ping  -c 1 ' .$ip );
}

	// Feedback for the end user
echo  "<pre>{$cmd}</pre>";

## 要求,利用命令执行getshell




