<?php
require('class/header.php');

if(!empty($_POST['user']) && !empty($_POST['pass']) && !empty($_POST['cpass'])){

	$user=filter(trim($_POST['user']));
	$pass=md5(trim($_POST['pass']));
	$cpass=md5(trim($_POST['cpass']));

	if(!get_magic_quotes_gpc()) { 
	        $user = addslashes($user);
	        $pass = addslashes($pass);
	        $cpass = addslashes($cpass);
	} 

	if($cpass!=$pass){
		echo "<script>alert('Confirm passwords and passwords are not equal')</script>";
		echo "<script>window.location.href='./register.php'</script>";
		exit;
	}

	$query="select * from users where username = '{$user}'";
	$result = $db->query($query);
	$num_results = $result->num_rows;

	if($num_results>0)
	{
		echo "<script>alert('This Username is exited!')</script>";
		echo "<script>window.location.href='./register.php'</script>";
		exit;

	}else{
		
		$query = "insert into users (username,password) values ('{$user}' , '{$pass}')";
		$result = $db->query($query);

		if($result){
			echo "<script>alert('Register success...')</script>";
			echo "<script>window.location.href='./login.php'</script>";
			exit;
		}else{
			echo "<script>alert('something error...')</script>";
		}
	}

	$db->close();
}
?>
<div class="container back">
<div class="row">
    <div class="col-md-8 col-md-offset-2 text-center">
      <h1 class="black">deserted place</h1>
      <h2 class="animated fadeInUp delay-05s black">rigeister pages</h2>
    </div>
</div>
<div class="window">
	<form method="post" class="form-signin" action="register.php">
		<div class="row">
		<h4 class="black">username:</h4><input type="text" class="form-control" name="user" >
		</div>
		<div class="row">
		<h4 class="black">password:</h4><input type="password" class="form-control"  name="pass" >
		</div>
		<div class="row">
		<h4 class="black">confirm password:</h4><input type="password" class="form-control"  name="cpass" >
		</div>
		<input type="submit" value="submit">
	</form>
</div>

<?php
	require('class/footer.php');
?>
