<?php
define("OLD_DRIVER", time());

function d_addslashes($array){
        foreach($array as $key=>$value){
        if(!is_array($value)){
            !get_magic_quotes_gpc() && $value=addslashes($value);
            waf($value);
            $array[$key]=$value;
        }
    }
    return $array;
}

function waf($value){
    $Filt = "\bUNION.+SELECT\b|SELECT.+?FROM";
    if (preg_match("/".$Filt."/is",$value)==1){
        die("found a hacker");
    }
}

$_POST=d_addslashes($_POST);
$_GET=d_addslashes($_GET);

include "common.php";

if (!$_SESSION["rank"]){
    header("Location: /");
    exit();
}
$file = $_GET['m'].".php";
if (!is_file($file)){
    die("404 error");
}


include_once($file);
?>