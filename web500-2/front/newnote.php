<?php


defined("DIR_PERMITION") or die("Permision denied!");

$userid=check_login();

if(!$userid){
     echo "<script>alert('not login!');</script>";
     echo("<script>location.href='./index.php?action=front&mode=login'</script>");
     
     die();

}elseif(isset($_POST['title'])&&isset($_POST['content'])&&isset($_POST['TOKEN'])){
     
     $title=htmlspecialchars(trim($_POST['title']));
     $content=htmlspecialchars(trim($_POST['content']));
     $TOKEN=$_POST['TOKEN'];

     if($TOKEN!=$_SESSION['CSRF_TOKEN']){
         die("token error!");
     }

     $sql="insert into `note` (title,content,userid) values ('$title','$content',$userid)";
     
     if(!empty($title)&&!empty($content)){
        
        $res=mysql_my_query($sql);
       
        if($res){
           
          echo("<script>alert('create success!')</script>");
          echo("<script>location.href='./index.php?action=front&mode=index'</script>");           
       }else{

         echo("<script>alert('create failed!')</script>");
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
  <link rel="alternate icon" type="image/png" href="./assets/i/favicon.png">
  <link rel="stylesheet" href="./assets/css/amazeui.min.css"/>
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
    .content{
      width:90%;
      margin:auto auto;


    }
  </style>
</head>
<body>
<div class="header">
  <div class="am-g">
    <h1>NS 笔记管理系统</h1>
    <p>username:<?php echo explode("|",$_COOKIE['uid'])[0];?><br/>userid:<?php echo $userid;?></p>
  </div>
  <hr />
</div>
<div class="content">
    <form class="am-form" method="post">
    <fieldset>
      <legend>创建笔记</legend>

      <div class="am-form-group">
        <label for="doc-ipt-email-1">笔记标题</label>
        <input type="text" class="" id="doc-ipt-email-1" placeholder="输入标题" name="title">
      </div>

      <div class="am-form-group">
        <label for="doc-ta-1">笔记内容</label>
        <textarea class="" rows="5" id="doc-ta-1" name="content"></textarea>
      </div>
      <input type="hidden" name="TOKEN" id="password" value="<?php echo $_SESSION['CSRF_TOKEN'];?>">

      <p><button type="submit" class="am-btn am-btn-default">提交</button></p>
    </fieldset>
    </form>
    
    <hr>
    <p>© NS 笔记管理系统.</p>
</div>
</body>
</html>
