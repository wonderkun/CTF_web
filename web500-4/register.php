
<?php
include_once "header.php";
$html=<<<html
<body>
username:&nbsp;&nbsp;<input name="username" id="username"></input>
<br><br>
password:&nbsp;&nbsp;<input name="password" type="password" id="password"></input>
<br><br>
email:&nbsp;&nbsp;<input name="email" id="email"></input>
<br><br>
<button id="register">Register</button>
<hr>
<div id="result"></div>
</body>
html;

if (isset($_GET['username'])&&isset($_GET['password'])&&isset($_GET['email'])){
	$username=$_GET['username'];
	$password=$_GET['password'];
	$email=$_GET['email'];	
	if(userExist($username) || emailExist($email)){
		myexit($callback,"username or email has registered.Please use another.");		
	}
	$token=md5(random_str());
	$stmt = $conn->prepare("INSERT into user(username,password,email,token,verified) VALUES (?,?,?,?,false)");
	//var_dump($stmt);
	$stmt->bind_param('ssss', $username,$password,$email,$token);
	$stmt->execute();
	$title="Register Our Fantastic Chat Room To Enjoy Life!";
	$content="Hello guy:<br><a href=".$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?username=$username&token=$token>Click here </a>to continue to register the wonderful CHAT APP";
	sendMail($title,$content,"admin",$email);
	exit('<script>alert("Email has been sent to your email,please comfirm to continue registration.");location.href="login.php";</script>');
}else if(isset($_GET['username'])&&isset($_GET['token'])){
	$username=$_GET['username'];
	$token=$_GET['token'];
	$stmt = $conn->prepare("UPDATE user SET verified=true where username= ? and token = ?");
	$stmt->bind_param('ss', $username,$token);
	$stmt->execute();	
	if($stmt->affected_rows==0){	
		exit("The link has been crafted.Maybe you are an attacker?");		
	}else{
		exit('<script>alert("register success.");location.href="login.php";</script>');
	}
}else{
		echo $html;
}
