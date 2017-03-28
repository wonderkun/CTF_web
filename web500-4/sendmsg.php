<?php 
include_once "config.php";
?>
<?php
$html=<<<body
<div>
tousername:&nbsp;&nbsp;<input name="touser" id="touser"></input>
<br><br>
message:<br>&nbsp;&nbsp;<textarea autofocus rows=3 cols=50 name="msg" id="msg" maxlength=256></textarea>
<br><br>
<button id="sendmsg">SendMessage</button>
</div>
<hr>
<div></div>
body;
//判断登录-》注册，找回密码
if(!isset($_SESSION['username'])){
	Header("Location: login.php");
}elseif(isset($_GET['touser'])&&isset($_GET['msg'])){
	$touser=$_GET['touser'];
	$msg=$_GET['msg'];
	sendmsg($_SESSION['username'],$touser,$msg);
	myexit($callback,"Send Success!");
}else{
	include_once "header.php";
	echo $html;
}
?>
