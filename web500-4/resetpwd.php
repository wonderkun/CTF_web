
<?php
include_once "config.php"
?>
<?php
if(isset($_SESSION['pwdreset'])&&$_SESSION['pwdreset']){
$html=<<<html1
<body>
username:<input name="username" id="reset_username"></input>
newpwd:<input name="newpwd"  type="password" id="newpwd"></input>	
<button id="resetpwd1">Reset My Password</button>
<hr>
<div id="result"></div>
</body>
html1;
}else{
$html=<<<html
<body>
username:<input name="username" id="reset_username"></input>
<button id="resetpwd">Reset My Password</button>
<hr>
<div id="result"></div>
</body>
html;
}
if(isset($_GET['username'])){
	$username=$_GET['username'];
	if(userExist($username)){
		//myexit($callback,"This username has been registered,please use another.");
	}else{
		myexit($callback,"no such username:$username");
	}
}else{
	include_once "header.php";
	exit($html);
}
if (!isset($_GET['token'])&&!isset($_GET['newpwd'])){
	$username=$_GET['username'];
	$stmt = $conn->prepare('SELECT username,email FROM user WHERE username = ?');
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$stmt->close();
	if($result->num_rows==0){
		myexit($callback,"No such user.");		
	}
	while ($row = $result->fetch_assoc()) {
		$email=$row['email'];
	}
	$stmt = $conn->prepare('SELECT username FROM  pwdreset WHERE (username = ? and used=false)');
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();	
	if($result->num_rows==0){
		$exist=false;
	}else{
		$exist=true;
	}
	
	$token=md5(random_str());
	if(!$exist){
		$stmt = $conn->prepare("INSERT into pwdreset(token,username,used) VALUES (?,?,false)");		
	}else{
		//$stmt = $conn->prepare("UPDATE  pwdreset set token=? WHERE  username=?");	
		$stmt = $conn->prepare("INSERT into pwdreset(token,username,used) VALUES (?,?,false)");
	}
	$stmt->bind_param('ss', $token,$username);
	$stmt->execute();
	$stmt->close();
	$title="Reset Your Password";
	$content="Hello $username:<br><a href=".$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?username=$username&token=$token>Click here </a>to continue to RESET you password.";
	sendMail($title,$content,"admin",$email);
	myexit($callback,'<script>alert(\"Email has been sent to your email,please comfirm to continue.\");location.href=\"login.php\";</script>');
}elseif(isset($_GET['token'])){
	$username=$_GET['username'];
	$token=$_GET['token'];
	$stmt = $conn->prepare("SELECT username FROM pwdreset WHERE username= ? and token = ? and used=false");
	$stmt->bind_param('ss', $username,$token);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows==0){	
		myexit($callback,"The link has been crafted.Maybe you wanna GAOSHI???");		
	}else{
		$_SESSION['username_rst']=$username;
		$_SESSION['pwdreset']=true;
		exit("<script>location.href='".$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."';</script>");
	}
}elseif(isset($_GET['newpwd'])){
	$username=$_SESSION['username_rst'];
	$newpwd=$_GET['newpwd'];
	$stmt = $conn->prepare("UPDATE user SET password=? WHERE username= ?");
	$stmt->bind_param('ss', $newpwd,$username);
	$stmt->execute();
	$affected_rows=$stmt->affected_rows;
	$stmt = $conn->prepare("UPDATE pwdreset SET used=true WHERE username= ?");
	$stmt->bind_param('s',$username);
	$stmt->execute();

	if($affected_rows==0){	
		myexit($callback,"Your password doesn\'t change.");		
	}else{
		$_SESSION['username_rst']=$username;
		$_SESSION['username']=$username;
		$_SESSION['pwdreset']=false;
		if(isAdmin($username))
			myexit($callback,"<script>location.href=\'index.php?flag=$flag\';</script>");
		else
			myexit($callback,"<script>location.href=\'index.php\';</script>");
	}
}
