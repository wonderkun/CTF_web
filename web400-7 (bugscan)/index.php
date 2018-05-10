<?php
include "common.php";


$connect=mysql_connect("127.0.0.1","root","i0ve*ctf") or die("connect to db error!");
mysql_select_db("taolu") or die("select db  error!");



$connect=mysql_connect("127.0.0.1","root","i0ve*ctf") or die("connect to db error!");
mysql_select_db("taolu") or die("select db  error!");


if (isset($_POST["name"])){
  $name = str_replace("'", "", trim($_POST["name"]));
  if (strlen($name) > 10){
    echo("<script>alert('too long')</script>");
  }else{
    $sql = "select count(*) from t_info where username = '$name' or nickname = '$name'";
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    if ($row == NULL){
      echo $sql;
    }
    if ($row[0]){
      $_SESSION["rank"] = "1";
      $url = "bugscan_c3d07ebeee7fbe19079bd7fca12ce8ed.zip";
      echo $url;
      header("Location: ./route.php?m=login");
      exit();
    }else{
      echo("<script>alert('go go go go ...')</script>");
    }  
  }
  
}

?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>是时候表演车技了 | 秋名山五菱宏光</title>
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
    <p>昨晚我在秋名山输给一辆五菱宏光 他用惯性飘移过弯 </p>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>弯道超车</h3>
    <hr>
    <br>

    <form method="post" class="am-form">
      <label>称号 or 名号</label>
      <input type="text"  name="name" value="">
      <br>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value="踩油门" class="am-btn am-btn-primary am-btn-sm am-fl">
      </div>
    </form>
    <hr>
    <p>© 2016 秋名山</p>
  </div>
</div>
</body>
</html>

