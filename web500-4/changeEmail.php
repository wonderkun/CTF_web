
<?php
include_once "header.php"
?>
<?php
if(!$_SESSION['username']){
	Header("Location: login.php");
}
if(!isset($_POST['newemail'])){
$html=<<<html
<body>
<form method="POST" action=''>
username:<input name="username" id="reset_username"></input>
newemail:<input name="newemail" id="newemail"></input>	
<button id="reset_email">Reset My Email</button>
</form>
<hr>
<div id="result"></div>
</body>
html;
exit($html);
}
if(isset($_POST['username'])){
	$username=$_POST['username'];
	if(userExist($username)){
		if($username!=$_SESSION['username'])
			exit("<script>alert('You cannot change other\'s email.');location.href=location.href;</script>");
	}else{
		exit("<script>alert('no such username:$username');location.href=location.href;</script>");
	}
}else{
	exit("<script>alert('Please tell me your username');location.href=location.href;</script>");
}
if(isset($_POST['newemail'])){
	$username=$_SESSION['username'];
	$newemail=$_POST['newemail'];
	$stmt = $conn->prepare("UPDATE user SET email=? WHERE username= ?");
	$stmt->bind_param('ss', $newemail,$username);
	$stmt->execute();
	$affected_rows=$stmt->affected_rows;
	if($affected_rows==0){	
		exit("<script>alert('Your email doesn\'t change.');</script>");		
	}else{	
		exit("<script>alert('Email has been modified to $newemail succefully.');location.href='index.php';</script>");
	}
}
