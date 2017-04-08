<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>Login Page | NPUSEC</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <meta name="renderer" content="webkit">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <link rel="alternate icon" type="image/png" href="assets/i/favicon.png">
  <link rel="stylesheet" href="assets/css/amazeui.min.css"/>
  <style>
    .header {
      text-align: center;
    }
    .header h1 {
      font-size: 200%;
      color: #333;
      margin-top: 30px;
    }
    .header p {
      font-size: 14px;
    }
  </style>
</head>
<body>
<div class="header">
  <div class="am-g">
    <h1>NPUSEC 网络管理系统</h1>
    <p>为写出好漏洞而生</p>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>登录</h3>
    <hr>
    <div class="am-btn-group">
      <a href="#" class="am-btn am-btn-secondary am-btn-sm"><i class="am-icon-github am-icon-sm"></i> Github</a>
      <a href="#" class="am-btn am-btn-success am-btn-sm"><i class="am-icon-google-plus-square am-icon-sm"></i> Google+</a>
      <a href="#" class="am-btn am-btn-primary am-btn-sm"><i class="am-icon-stack-overflow am-icon-sm"></i> stackOverflow</a>
    </div>
    <br>
    <br>

    <form method="post"  action ="#" class="am-form">
      <label for="username">用户名:</label>
      <input type="text" name="username" id="username" value="">
      <br>
      <label for="password">密码:</label>
      <input type="password" name="password" id="password" value="">
      <br>
      <label for="remember-me">
        <input id="remember-me" type="checkbox">
        记住密码
      </label>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm am-fl">
        <input type="submit" name="" value="忘记密码 ^_^? " class="am-btn am-btn-default am-btn-sm am-fr">
      </div>
    </form>
    <hr>
    <p>© 2017 NPUSEC.</p>
  </div>
</div>
</body>
</html>



<?php
session_start();
error_reporting(0);

include("config.php");

header("Content-Type:text/html;charset=utf-8");

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

$_POST = d_addslashes($_POST);
$_GET =  d_addslashes($_GET);
$username =isset($_POST['username'])?$_POST['username']:die();
$password = isset($_POST['password'])?md5($_POST['password']):die();
$sql="select password from users  where username='$username'";
$result = $conn->query($sql);

if(!$result){
    die('<script>alert("用户名或密码错误!!")</script>');
}

$row = $result->fetch_assoc();

if($row[0] === $password){
    $_SESSION['username']=$username;
    $_SESSION['status']=1;
    header("Location:./ping.php");
    
}else{

   die("<script>alert('用户名或密码错误!!')</script>");
}

?>
