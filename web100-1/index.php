<?php
// error_reporting(E_ALL || ~E_NOTICE);

function strreplace($str){
$str = str_replace('`','',$str);
$str = str_replace(';','',$str);
$str = str_replace('|','',$str);
$str = str_replace('&','',$str);
$str = str_replace('>','',$str);
$str = str_replace(')','',$str);
$str = str_replace('(','',$str);
$str = str_replace(')','',$str);
$str = str_replace('{','',$str);
$str = str_replace('}','',$str);
$str = str_replace('%','',$str);
$str = str_replace('#','',$str);
$str = str_replace('!','',$str);
$str = str_replace('?','',$str);
$str = str_replace('@','',$str);
$str = str_replace('+','',$str);
return $str;
}
if($_GET['num']<>""){

$num = $_GET['num'];
if(strstr($num,'1')){
die("Sorry");
}elseif($num <> 1){
echo "Try to num = 1";
}

if($num == 1 ){
echo "Flag in http://127.0.0.1/flag.php"."</br>";
$cmd=trim($_GET['cmd']);

$cmd=strreplace($cmd);

system("curl$cmd/flag.php");

}
}else{echo "It Works!";}
?>