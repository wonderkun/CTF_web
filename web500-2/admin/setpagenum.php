<?php
defined("DIR_PERMITION") or die("Permision denied!");
$userid=check_login();
$level=get_level();

if($userid!==false&&$level!==false){
      if(isset($_POST['page'])&&isset($_POST['TOKEN'])){
          $page=$_POST['page'];
          $TOKEN=$_POST['TOKEN'];

          if($TOKEN!=$_SESSION['CSRF_TOKEN']){
            die("token error!");
          }

          if(!is_numeric($page)){
              die("page must be a number!");   
          }
          if($page<1) $page=1;

          $sql="update page set num=$page";
          $res=mysql_my_query($sql);
          if($res){
                echo "<script>alert('update  success!');</script>";
                echo("<script>location.href='./index.php?action=admin&mode=index'</script>");

          }else{
               echo "<script>alert('update  fail!');</script>";
               die();
          }
      }
}else{

    echo "<script>alert('not login!');</script>";
    echo("<script>location.href='./index.php?action=admin&mode=login'</script>");
    die();
    // $result=mysql_my_query($sql);
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
    <h3>设置显示条数</h3>
    <br>
    <br>
    <form method="post" class="am-form">
      <label for="page">设置条数:</label>
      <input type="text" name="page" id="page">
      <br>
      <div class="am-cf">
        <input type="submit" name="" value="设 置" class="am-btn am-btn-primary am-btn-sm am-fl">
        <input type="hidden" name="TOKEN" id="TOKEN" value="<?php echo $_SESSION['CSRF_TOKEN'];?>">

      </div>
    </form>
    <hr>
    <p>© NS 笔记管理系统.</p>
  </div>
</div>
</body>
</html>