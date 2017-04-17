<?php 

error_reporting(0);
session_start();

$dbhost = "127.0.0.1";
$dbuser = "admin";
$dbpass = "password987~!@";
$dbname = "dsqli";

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$conn ->query("set names utf8"); 

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


function filter($id){
    $id = strtolower($id);
    
	$id = str_replace('select', '', $id);
	$id = str_replace('update', '', $id);
	$id = str_replace('insert', '', $id);
	$id = str_replace('delete', '', $id);
	$id = str_replace('and', '', $id);
	$id = str_replace('or', '', $id);
	$id = str_replace('where', '', $id);
	$id = str_replace('union', '', $id);
    $id = str_replace('like', '', $id);
    $id = str_replace('regexp', '', $id);
    $id = str_replace('is', '', $id);
	$id = str_replace('=', '', $id);
	$id = str_replace(',', '', $id);
	$id = str_replace('|', '', $id);
	$id = str_replace('&', '', $id);
	$id = str_replace('!', '', $id);
    $id = str_replace('%', '', $id);
	$id = str_replace('^', '', $id);
	$id = str_replace('<', '', $id);
	$id = str_replace('>', '', $id);
	$id = str_replace('*', '', $id);
	$id = str_replace('(', '', $id);
	$id = str_replace(')', '', $id);
    return $id ;
}

function random_str($length = "32")
{
    $set = array("a", "b", "c",  "d", "e", "f", 
        "g", "h", "i", "j", "k", "l",
        "m","n", "o", "p", "q", "r","s","t","u","v", "w","x",
        "y","z","1", "2", "3", "4", "5", "6", "7", "8", "9");
    $str = '';
    for ($i = 1; $i <= $length; ++$i) {
        $ch = mt_rand(0, count($set) - 1);
        $str .= $set[$ch];
    }
    return $str;
}
