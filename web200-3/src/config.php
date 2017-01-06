<?php 
$dbhost="localhost";

$dbuser="admin";
$dbpasswd="password";
$db="npusec";


$conn=mysqli_connect($dbhost,$dbuser,$dbpasswd,$db) or die ("数据库连接出错");
$conn->query("set names utf-8");


function d_addslashes($array){

    foreach($array as $key=>$value){
        if(!is_array($value)){
              !get_magic_quotes_gpc()&&$value=addslashes($value);
              $array[$key]=$value;
        }else{

          $array[$key]=d_addslashes($array[$key]);
        }
    }
    return $array;

}




?>