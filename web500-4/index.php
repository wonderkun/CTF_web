
<?php 
include_once "header.php";
$a=<<<body
<div>Welcome <b id='name'></b> to Secure ChatRoom!</div>
<center><br>All the message was deleted after it has been read!<br> Have fun!<br></center>
body;
//ÅÐ¶ÏµÇÂ¼-¡·×¢²á£¬ÕÒ»ØÃÜÂë
if(!$_SESSION['username']){
	Header("Location: login.php");
}
if(isset($_GET['flag'])){
	Header("Location: index.php");
}
if($_SESSION['isAdmin']){
	//echo "Welcome admin";
}
echo $a;
?>
