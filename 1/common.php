<?php
error_reporting(2);
session_start();
$connect = mysql_connect("127.0.0.1", "root", "r00t") or dir("error 1");
mysql_select_db("autumn") or die("error 2");



function is_login(){
    $sid = $_COOKIE["sid"];
    $data = explode("|",$sid);
    if($data[0] && $data[1] && $data[1] == encode($data[0]))
    {
        return $data[0];
    }

    return FALSE;
}

function encode($str){
    $key = sha1("怎么可能会告诉你");
    return md5($key.$str);
}
function set_login($name){
    $data = encode($name);
    setcookie("sid","$name|$data");
}



?>