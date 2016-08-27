<?php
defined('OLD_DRIVER') or exit('Access Invalid!');

?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>成功登陆 | 秋名山五菱宏光</title>
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
    <br><br><br><br>
    <p>是时候登顶秋名山了</p>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
  
  <?php
  $user = is_login();
  waf($user);
  
  if(!$user){
    echo "<h3>not login<h3>";
  }
  else{
    $sql = "select nickname from t_user where username = '$user' limit 1";
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    echo "Welcome $row[0]";
  } 
  ?>
 
    <hr>
    <p>© 2016 秋名山</p>
  </div>
</div>
</body>
</html>

