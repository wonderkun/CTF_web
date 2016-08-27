<?php
 defined("DIR_PERMITION") or die("Permision denied!");
 
 if(isset($_POST['uname'])&&isset($_POST['password'])&&isset($_POST['TOKEN'])){

    $uname=$_POST['uname'];
    $password=md5($_POST['password']);
    $TOKEN=$_POST['TOKEN'];

    if($TOKEN!=$_SESSION['CSRF_TOKEN']){
        die("token error!");
    }
    $sql="select count(*) count from  user where uname='$uname'";
    
    $res=mysql_my_query($sql);
    $row=$res->fetch_assoc(); //获取第一条记录

    if($row['count']){
        
        echo("<script>alert('username  repeats!')</script>");

    }else{

        $sql="insert into `user`(uname,password,level) values ('$uname','$password',0)";
        $res=mysql_my_query($sql);
        if($res){
            header("Location: ./index.php?action=front&mode=login");
            exit();

        }else{

          echo("<script>alert('register failed!')</script>");
        }     
        
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
    <h1>NS 笔记管理系统</h1>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>注册</h3>
    <br>
    <br>

    <form method="post" class="am-form">
      <label for="uname">用户名:</label>
      <input type="text" name="uname" id="email" value="">
      <br>
      <label for="password">密码:</label>
      <input type="password" name="password" id="password" value="">
      <br>
      <input type="hidden" name="TOKEN" id="password" value="<?php echo $_SESSION['CSRF_TOKEN'];?>">
      <br>
      <br />
      <div class="am-cf">
        <input type="submit" name="" value="注 册" class="am-btn am-btn-primary am-btn-sm am-fl">
        <li class="am-btn am-btn-default am-btn-sm am-fr"><a href="index.php?action=front&mode=login">已有账号 ^_^?</a></li>
      </div>
    </form>
    <hr>
    <p>© NS 笔记管理系统.</p>
  </div>
</div>
</body>
</html>