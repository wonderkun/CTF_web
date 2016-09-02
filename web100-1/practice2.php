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
$str = str_replace('/','',$str);


$str = str_replace('.','',$str);
$str = str_replace(':','',$str);  //添加这两句  

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

    // var_dump("curl".$cmd."flag.php");

    system("curl".$cmd."flag.php");
    }

}else{echo "It Works!";}

//$cmd=$IFS\http$IFS\-x$IFS\0x7F000001$IFS\-T$IFS\

?>