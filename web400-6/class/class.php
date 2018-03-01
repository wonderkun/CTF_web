<?php
require('config.php');

function filter($string){
	$safe = array('select', 'insert', 'update', 'delete', 'where');
 	$safe = '/' . implode('|', $safe) . '/i';
 	$string = preg_replace($safe, '', $string);

	$xsssafe = array('img','script','on','svg','link');
	$xsssafe = '/' . implode('|', $xsssafe) . '/i';
	return preg_replace($xsssafe, '', $string);
		

}

function createRandomStr($length){ 
	$str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$strlen = 62; 
	while($length > $strlen){ 
		$str .= $str; 
		$strlen += 62; 
	} 
	$str = str_shuffle($str); 

	return substr($str,0,$length); 
} 


function select($user){
	global $db;

	$query = "select * from users where username = '{$user}'";
	$result = $db->query($query);

	$req = $result->fetch_assoc();

	return $req;
}

ini_set('date.timezone','Asia/Shanghai');
ini_set("session.cookie_httponly", 1);
session_start();


header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'");

$flag = "hctf{S0m3_iz_fun_m3thod3ed4rod}";

if( isset($_SESSION['user']) && $_SESSION['user'] === 'hctf_admin_LoRexxar2e23'){
	setcookie("flag",$flag);
}
?>
