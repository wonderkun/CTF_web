<?php
    if(isset($_GET["include"])){
        set_include_path(get_include_path() . PATH_SEPARATOR . $_GET["include"]);
    }
    ini_set("display_errors", "Off");
    spl_autoload_register();
    error_reporting(0);
    session_start();

    $piclist = isset($_COOKIE["piclist"]) ? unserialize($_COOKIE["piclist"]) : [];
    
    function getUser(){
        if(!isset($_SESSION["token"])){
            return -1;  //not login
        }else if($_SESSION["token"] == "0"){
            return 0;   //Administrator
        }else{
            $key = base64_decode($_SESSION["token"]);
            $user = explode("~:", $key)[0]; //user
            if(!$user){
                return -1;
            }
            return $user;
        }
    }
    
    function randmd5()
    {
        $len = 16;
        $ret = "";  
        for ($i = 0; $i < $len; $i++)
        {  
            $ret .= chr(mt_rand(0, 255));
        }
        return md5($ret);
    }
?>
