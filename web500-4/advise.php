<?php 
include_once "config.php";
?>
<?php
$html=<<<body
<div>
Advise:<br>&nbsp;&nbsp;<textarea autofocus rows=3 cols=50 name="msg" id="msg" maxlength=256></textarea>
<br><br>
<button id="advise">Tell US Now!</button>
</div>
<hr>
<div></div>
body;
//?§Ø???-????????????
if(!isset($_SESSION['username'])){
	Header("Location: login.php");
}elseif(isset($_GET['msg'])){
	$touser=getAdmin();
	$msg=$_GET['msg'];
	sendmsg($_SESSION['username'],$touser,$msg);
	myexit($callback,"Send Success!");
}else{
	include_once "header.php";
	echo $html;
}
?>
