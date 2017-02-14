<?php

require_once("common.php");
session_start();

if (@$_SESSION['login'] === 1)
{
    header('Location:/web/riji.php');
	exit();
}

if(@$login==1)
{
	
	@mysql_conn();
	$sql = "select * from user where name='$username'";
	$result = @mysql_fetch_array(mysql_query($sql));
	mysql_close();
	if (!empty($result))
	{
		
		if($result['passwd'] == md5($password))
		{
			$user_cookie = '';
			$user_cookie .= $result['userid'];
			$user_cookie .= $result['name'];
			$user_cookie .= $result['salt'];
			$cookies = base64_encode($user_cookie);
			//$cookies = $user_cookie;
			setcookie("user",$cookies,time()+60,'/web/');
			$_SESSION['login'] = 1;
			$_SESSION['user'] = $username;
			header('Location:/web/riji.php');
		}
		else
		{
			echo("<script>alert('Password Worng?')</script>");
		}
	}
	else
	{
		echo("<script>alert('Username Worng?')</script>");
	}
}
if(@$regi == 1){
	if(@$mibao && @$username && @$password)
	{
		mysql_conn();
		$sql1 = "select * from user where name='$username'";
		$result1 = mysql_fetch_row(mysql_query($sql1));
		mysql_close();
		if (!empty($result1)){
			echo('<script>alert("The user has been registered!")</script>');
		}
		else{
			$salt = get_salt();
			mysql_conn();

			$sql2 = "INSERT INTO user(`name`,`passwd`,`check`,`salt`) VALUES('$username',md5('$password'),'$mibao','$salt')";
			if(!mysql_query($sql2)){
				
				echo("<script>alert('Register Wrong!!')</script>");
			}
			else{
				echo("<script>alert('Register Success!!')</script>");
			}
			mysql_close();
		}

	}
	else
	{
		echo("<script>alert('check your enter!!!')</script>");
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
<link rel="stylesheet" href="./css/index.css"/>
<link rel="stylesheet" href="./css/style.css"/>
<link rel="stylesheet" href="./css/animate.css"/>
<script type="text/javascript" src="./js/jquery1.42.min.js"></script>
<script type="text/javascript" src="./js/jquery.SuperSlide.2.1.1.js"></script>
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
       <div class="login" id="content">
         <form action="index.php" method="post" >
			用户名:   <input type="text" name="username" placeholder="用户名" required="required" /></br>
			密 码：   <input type="password" name="password" placeholder="密码" required="required" /></br>
			密 保：   <input type="password" name="mibao" placeholder="密保" /></br>
			<button type="submit" class="btn btn-primary btn-block btn-large" name="login" value=1>登陆</button>
			<button type="submit" class="btn btn-primary btn-block btn-large" name="regi" value=1>注册</button>
		 </form>
       </div>
</body>
</html>
