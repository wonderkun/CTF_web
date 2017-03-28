<?php 
include_once "config.php";
?>
<?php
$a=<<<body
username:&nbsp;&nbsp;<input name="username" id="login_username"></input>
<br><br>
password:&nbsp;&nbsp;<input name="password" type="password" id="login_password"></input>
<br><br>
<button id="login">login</button>
<hr>
<div id="result"></div>
</body>
body;
if (isset($_GET['username'])&&isset($_GET['password'])){
	$username=$_GET['username'];
	$password=$_GET['password'];
	$stmt = $conn->prepare('SELECT username FROM  user WHERE username = ? and password= ? and verified=true');
	$stmt->bind_param('ss', $username,$password);
	$stmt->execute();
	$result = $stmt->get_result();
	//var_dump($result);
	if($result->num_rows==0){
		myexit($callback,"username doesn\'t exist or password error!");
	}	
	while ($row = $result->fetch_assoc()) {
		$_SESSION['username']=$row['username'];
		if(isAdmin($_SESSION["username"])){
			$_SESSION['isAdmin']=true;
		}
		//var_dump($row);// do something with $row
	}
	myexit($callback,"<script>location.href=\'index.php\';</script>");
}else{
	include_once "header.php";
	exit($a);
}

?>