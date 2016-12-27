<?php
defined('OLD_DRIVER') or exit('Access Invalid!');

if (!isset($_POST['username']) or !isset($_POST['password'])){

}else{
  $name = $_POST['username'];
  $pwd = md5($_POST['password']);
  $sql = "select count(*) from t_user where username = '$name' and password='$pwd'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  var_dump($row);
  
  if ($row[0]){
    set_login($name);
    header("Location: ./route.php?m=user");
    exit();
  }else{
    echo("<script>alert('sad')</script>");
  }
}

?>

<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>登机 | 秋名山五菱宏光</title>
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
    <h1>老司机登机</h1>
    <p>不想开飞机的司机不是老司机</p>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>登机口</h3>
    <hr>
    <br>

    <form method="post" class="am-form">
      <label>乘客名:</label>
      <input type="text"  name="username" value="">
      <br>
      <label>机票:</label>
      <input type="password" name="password" value="">
      <br>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value="开 车" class="am-btn am-btn-primary am-btn-sm am-fl">
        <li class="am-btn am-btn-default am-btn-sm am-fr"><a href="route.php?m=register">没有票 ^_^?</a></li>
        
      </div>
    </form>
    <hr>
    <p>© 2016 秋名山</p>
  </div>
</div>
</body>
</html>
