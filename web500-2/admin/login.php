<?php

defined("DIR_PERMITION") or die("Permision denied!");

if(isset($_POST['uname'])&&isset($_POST['password'])&&isset($_POST['TOKEN'])){

    $uname="admin";
    $password=md5($_POST['password']);
    $TOKEN=$_POST['TOKEN'];

    if($TOKEN!=$_SESSION['CSRF_TOKEN']){
        die("token error!");
    }
    $sql="select id,level  from  user where uname='$uname' and password='$password' and level='1'";
    
    $res=mysql_my_query($sql);
    $row=$res->fetch_assoc(); //获取第一条记录

    if($row['id']){
        
        set_login($uname,$row['id'],$row['level']);
        
        header("Location: ./index.php?action=admin&mode=index");
        exit();
    }else{
                
        echo("<script>alert('username or password error!')</script>");
    }

}

?>

<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>NS | 笔记管理系统</title>
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
    <h1>NS 笔记管理系统后台</h1>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>管理员登录</h3>
    <br>
    <br>

    <form method="post" class="am-form">
      <label for="uname">用户名:</label>
      <input type="text" name="uname" id="uname" value="admin" readonly>

      <br>
      <label for="password">密码:</label>
      <input type="password" name="password" id="password" value="">
      <br>
      <input type="hidden" name="TOKEN" id="password" value="<?php echo $_SESSION['CSRF_TOKEN'];?>">
      <br>
      <label for="remember-me">
        <input id="remember-me" type="checkbox">
        记住密码
      </label>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm am-fl">

      </div>
    </form>
    <hr>
    <p>© NS 笔记管理系统.</p>
  </div>
</div>
</body>
</html>


