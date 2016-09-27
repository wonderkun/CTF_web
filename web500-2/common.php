<?php

include_once("config.inc.php");

function rand_str($lenth=16){
   $rand=[];
   $_str="qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM";
   while($lenth){
   $rand[]=$_str[rand(0,strlen($_str)-1)];
   $lenth--;
   }
//    var_dump($rand);

   return implode($rand);
}

// echo rand_str();

if(!isset($_SESSION['SECURITY_KEY'])){

    $_SESSION['SECURITY_KEY']=rand_str(6);

}
if(!isset($_SESSION['CSRF_TOKEN'])){
    $_SESSION['CSRF_TOKEN']=rand_str(16);

}

if(!isset($_SESSION['level'])){

   $_SESSION['level']=null;
}


if(!isset($_SESSION['userid'])){
   $_SESSION['userid']=null;
}



function mysql_my_query($sql){
        global $conn;
        $res=$conn->query($sql) or die("查询数据库出错!");
        
        return $res;
        
}

function encode($str){
    return md5($_SESSION['SECURITY_KEY'].$str);

}

function set_login($uname,$id,$level){
     $_SESSION['userid']=$id;
     $_SESSION['level']=$level;
     
     $endata=encode($uname);
     setcookie("uid","$uname|$endata");
     
}

function check_login(){

    $uid=$_COOKIE['uid'];
    $userinfo=explode("|",$uid);

    if($userinfo[0]&&$userinfo[1]&&$userinfo[1]==encode($userinfo[0])){
        return $_SESSION['userid'];

    }else{

        return FALSE;

    }

}

function get_level(){

    $uid=$_COOKIE['uid'];
    $userinfo=explode("|",$uid);

    if($userinfo[0]&&$userinfo[1]&&$userinfo[1]==encode($userinfo[0])){
        
        if($_SESSION['level']!=="0"){

            return $_SESSION['level'];
        }else{
            return FALSE;

        }
    }else{

        return FALSE;
    }

}

// var_dump($_SESSION);

function get_page_size(){

      $sql="select num from page";
      $res=mysql_my_query($sql);
      $row=$res->fetch_assoc();
      return $row['num'];
}

function set_page_size(){
    
    $sql="update page set num=20";
    $res=mysql_my_query($sql);
    
}

function get_uname($userid){
       
       $sql="select uname from user where id='$userid'";
       $res=mysql_my_query($sql);
       $row=$res->fetch_assoc();
      return  htmlspecialchars($row['uname']);

}

