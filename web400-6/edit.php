<?php
require('class/header.php');

if(!isset($_SESSION['user']))
	{
		echo "<script>alert('you need login first!')</script>";
		echo "<script>window.location.href='./index.php'</script>";
		exit;	
	}

$user = $_SESSION['user'];

// $csrftoken = substr(md5($user.createRandomStr(8)),4,8);
// $_SESSION['csrftoken'] = $csrftoken;


$callback = $_GET['callback'];	
preg_match("/\w+/i", $callback, $matches);

if($matches[0] === "EditProfile"){

	$guser = $user;
	$query = "select * from users where username = '{$guser}'";
	$result=$db->query($query);
	$row = $result->fetch_assoc();

}else{
	if(empty($_GET['user'])){

		$rid = rand(1,9);
		$query = "select * from users limit {$rid},1";
		$result=$db->query($query);
		$row = $result->fetch_assoc();
		$guser = $row['username'];

		header("location: edit.php?callback=RandomProfile&user=".$guser);
		exit;

	}else{
		$guser = $_GET['user'];
		$query = "select * from users where username = '{$guser}'";
		$result=$db->query($query);
		$result_num=$result->num_rows;

		if($result_num==0){
			exit("what are you doing???");
		}

		$row = $result->fetch_assoc();
	}
}


if(empty($row['email'])){
	$email = 'None';
}else{
	$email = $row['email'];
}

if(empty($row['message'])){
	$message = 'None';
}else{
	$message = $row['message'];
}

?>

<div class='col-md-8 col-md-offset-2 text-center head' id="head">
<h1>Profile edit page</h1>
</div>

<div id='hide' class='col-md-8 col-md-offset-2 text-center'><h2 class='animated fadeInUp delay-05s white'>Magical area</h2></div>

<div class="container back">

<div class="list-group-item warn edit">
<h3>
Tips:
</h3>
<p>
Hello <?=$user?>,Oh! it is a Magical area, something happened.</p>
</div>

<div class="list-group-item edit">

<form class="form-signin">
	<div class="row">
	<h4 class="black">username:</h4><input type="text" class="form-control" id="user" name="user" readonly="readonly" value="<?=htmlspecialchars($guser)?>">
	</div>
	<div class="row">
	<h4 class="black">email:</h4><input type="text" class="form-control" id="email" name="email" value="<?=htmlspecialchars($email)?>">
	</div>
	<div class="row">
	<h4 class="black">message:</h4><textarea type="text" class="form-control" id="mess" name="message" rows="3"><?=htmlspecialchars($message)?></textarea>
	</div>
</form>

<script>
function UpdateProfile(){
	var username = document.getElementById('user').value;
	var email = document.getElementById('email').value;
	var message = document.getElementById('mess').value;

	window.opener.document.getElementById("email").innerHTML="Email: "+email;
	window.opener.document.getElementById("mess").innerHTML="Message: "+message;

	console.log("Update user profile success...");
	window.close();
}

function EditProfile(){
	document.onkeydown=function(event){
		if (event.keyCode == 13){
			UpdateProfile();
		}
	}
}

function RandomProfile(){
	setTimeout('UpdateProfile()', 1000);
}

</script>
</div>
</div>

<?php
$db->close();

echo "<script>";
echo $matches[0]."();";
echo "</script>";

require('class/footer.php');
?>
