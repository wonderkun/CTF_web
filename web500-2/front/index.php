<?php

defined("DIR_PERMITION") or die("Permision denied!");

$userid=check_login();

if(!$userid){

    echo "<script>alert('not login!');</script>";
    echo("<script>location.href='./index.php?action=front&mode=login'</script>");
    die();
}else{

    $sql="select * from note where userid='$userid' or userid='1'";
    $result=mysql_my_query($sql);
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

  <a class="am-btn am-btn-success" style="float:right;margin:0 0 20px 0" href="./index.php?action=front&mode=newnote" >新建笔记</a>
   <table class="am-table am-table-bordered am-table-radius am-table-striped am-table-hover" >
    <thead>
        <tr>
            <th>title</th>
            <th>content</th>
            <th>delete</th>
        </tr>
    </thead>
    <tbody>

        <?php 
             while($row=$result->fetch_assoc()){
               echo "<tr>";
               echo "<td>".$row['title']."</td>";
               echo "<td>".$row['content']."</td>";
               echo "<td><a href=./index.php?action=front&mode=delete&id=".$row['id']."&TOKEN=".$_SESSION['CSRF_TOKEN'].">delete</a></td>";
               echo "</tr>";
             }

        ?>
   
    </tbody>
</table>
    <hr>
    <p>© NS 笔记管理系统.</p>
</div>
</body>
</html>