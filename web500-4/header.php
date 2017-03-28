<html>
<head>
<link href="css/css.css" rel="stylesheet" type="text/css" />
<script src="js/jquery.js"></script>
<script src="js/common.js"></script>
</head>
<?php
include_once "config.php"
?>
<?php
if(!isset($_SESSION['username'])){
	echo <<<notlogin
	<a href="register.php">Register</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="login.php">Login</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
notlogin;
}else{
	echo "Welcome ".$_SESSION['username'];
	echo <<<login
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="index.php">Home Page</a>	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="sendmsg.php">SendMessage</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
	<a href="showmsg.php">MyMessage</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="advise.php">Give Some Advise</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="changeEmail.php">ChangeEmail</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
	<a href="logout.php">Logout</a>	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
login;
}
echo <<< resetpwd
<a href="resetpwd.php">ResetPassword</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
resetpwd;

echo "<hr>";
?>
