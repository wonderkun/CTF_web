<?php

define("DIR_PERMITION",time());
// phpinfo();

function d_addslashes($array){

    foreach($array as $key=>$value){
        if(!is_array($value)){
              !get_magic_quotes_gpc()&&$value=addslashes($value);
              $array[$key]=$value;
        }else{

          $array[$key] = d_addslashes($array[$key]);
        }
    }
    return $array;

}

$_POST=d_addslashes($_POST);
$_GET=d_addslashes($_GET);


include_once('common.php');

if(!isset($_GET['action'])||!isset($_GET['mode'])){

    header("Location: ./index.php?action=front&mode=login");

}elseif(!preg_match('/\.{2}/is',$_GET['action'])&&preg_match('/^[0-9A-Za-z]+$/is',$_GET['mode'])){
    $action=$_GET['action'];
    $mode=$_GET['mode'];
    $file=$action.'/'.$mode.'.php';
    
    // echo $file;

}else{

    die("Invalid Request!");
}

include($file);
