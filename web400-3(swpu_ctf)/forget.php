<?php

require_once("common.php");
session_start();

if (@$_SESSION['login'] === 1)
{
    header('Location:/web/riji.php');
	exit();
}
if(@$forget==1)
{
	
	@mysql_conn();
	$sql = "select * from user where name='$username'";
	$result = @mysql_fetch_array(mysql_query($sql));
	mysql_close();
	if (!empty($result))
	{
		
		if($result['salt'])
		{
			$check = base64_encode(md5($result['salt']));
			$name = $result['name'];
			header("Location:/web/repass.php?username=$name&check=$check&mibao=$mibao&pass=$pass");
		}
		else
		{
			echo("<script>alert('Get salt Worng?')</script>");
		}
	}
	else
	{
		echo("<script>alert('Please check!!?')</script>");
	}
}
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>日记系统</title>
<meta name="keywords" content="日记系统" />
<meta name="description" content="" />
<link rel="stylesheet" href="css/index.css"/>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="css/animate.css"/>
<script type="text/javascript" src="js/jquery1.42.min.js"></script>
<script type="text/javascript" src="js/jquery.SuperSlide.2.1.1.js"></script>
<!--[if lt IE 9]>
<script src="js/html5.js"></script>
<![endif]-->
</head>

<body>
      <!--header start-->
    <div id="header">
      <h1>日记系统</h1>
      <p>一个给小美的日记系统</p>    
    </div>
     <!--header end-->
    <!--nav-->
     <div id="nav">
         <ul>
         <li><a href="index.php">登陆</a></li>
		 <li><a href="forget.php">找回密码</a></li>
         <li><a href="riji.php">个人日记</a></li>
         <li><a href="guestbook.php">写日记</a></li>
		 <li><a href="logoff.php?off=1">注销</a></li>
         <div class="clear"></div>
        </ul>
      </div>
       <!--nav end-->
    <!--content start-->
    <div id="content">
       <!--left-->
         <div class="left" id="guestbook">
           <div class="weizi">
           <div class="wz_text">当前位置：<a href="#">首页</a>><h1>写日志</h1></div>
           </div>
           <div class="g_content">
             <form action="forget.php" method="POST">
				用户名:   <input type="text" name="username"mibao placeholder="用户名" required="required" /></br>
				密 保:   <input type="text" name="mibao" placeholder="密保" required="required" /></br>
				新密码:   <input type="text" name="pass" placeholder="new password" required="required" /></br>
				<button type="submit" class="btn btn-primary btn-block btn-large" name="forget" value=1>找回</button>
			</form>
           </div>
         </div>
         
    </div>
</body>
</html>

