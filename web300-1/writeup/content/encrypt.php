<?php

require_once('key.php');

define('BS', 16);

function getRandChar($length){
	$str = null;
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	for($i=0;$i<$length;$i++){
		$n = rand(0, strlen($strPol) - 1);
		$str.=$strPol[$n];
	}
	return $str;
}

function pad($str) {
	return $str . str_repeat(chr(BS - strlen($str) % BS), (BS - strlen($str) % BS));
}

function unpad($str) {
	return substr($str, 0, -ord(substr($str, -1, 1)));
}

function token_encrypt($str) {
	$key = get_key();
	srand(time() / 300);
	$iv = getRandChar(16);
	return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, pad($str), MCRYPT_MODE_CFB, $iv));
}

function token_decrypt($str) {
	$key = get_key();
	srand(time() / 300);
	$iv = getRandChar(16);
	return unpad(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, hex2bin($str), MCRYPT_MODE_CFB, $iv));
}

?>