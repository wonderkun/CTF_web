<?php


defined("DIR_PERMITION") or die("Permision denied!");

$userid=check_login();
$level=get_level();

if($userid!==false&&$level!==false){
  
    $page_size=get_page_size();
    //默认仅仅显示 前$page_size条数据 
    $sql="select * from note  limit 0,".$page_size;
    $result=mysql_my_query($sql);

    set_page_size(); #设置default page size 

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
    <h1>NS 笔记管理系统后台</h1>
    <p>welcome:admin<br/>userid:1</p>
  </div>
  <hr />
</div>

<div class="content">

  <a class="am-btn am-btn-success" style="float:right;margin:0 0 20px 0" href="./index.php?action=admin&mode=setpagenum">前<?php echo $page_size;?>条笔记</a>
   <table class="am-table am-table-bordered am-table-radius am-table-striped am-table-hover" >
    <thead>
        <tr>
            <th>user</th>
            <th>title</th>
            <th>content</th>
        </tr>
    </thead>
    <tbody>

        <?php 

             while($row=$result->fetch_assoc()){
               echo "<tr>";
               echo "<td>".get_uname($row['userid'])."</td>";
               echo "<td>".$row['title']."</td>";
               echo "<td>".$row['content']."</td>";
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

