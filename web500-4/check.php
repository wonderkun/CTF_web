<?php
include_once "config.php"
?>
<?php
if(isset($_GET['email'])){
	$email=$_GET['email'];
	if(emailExist($email)){
		myexit($callback,"This email has been registered,please use another.");
	}
}elseif(isset($_GET['username'])){
	$username=$_GET['username'];
	if(userExist($username)){
		myexit($callback,"This username has been registered,please use another.");
	}else{
		myexit($callback,'username ok.');
	}
}elseif($_SESSION['username']){
	myexit($callback,$_SESSION['username']);
}