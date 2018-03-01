<?php
require('class/header.php');

if(!empty($_SESSION['user'])){
	echo "<script>alert('This user has logged in');</script>";
	echo "<script>window.location.href='./user.php'</script>";
	exit;
}


if(!empty($_POST['user']) && !empty($_POST['pass'])){

	$user=trim($_POST['user']);
	$pass=md5(trim($_POST['pass']));

	if(!get_magic_quotes_gpc()) { 
        $user = addslashes($user);
        $pass = addslashes($pass);
    } 
	
	$query="select password from users where username = '{$user}'";
	$result=$db->query($query);
	$result_num=$result->num_rows;

	if($result_num==0)
	{
		echo "<script>alert('username or password is wrong!')</script>";
		echo "<script>window.location.href='./login.php'</script>";
		exit;	
	}

	else
	{
		$row=$result->fetch_assoc();
		$password=$row['password'];
		if($pass==$password)
		{
			$_SESSION['user'] = $user;
			header("location:./user.php");
			exit;
		}
		else
		{
		echo "<script>alert('username or password is wrong!')</script>";
		echo "<script>window.location.href='./login.php'</script>";
		exit;	
		}
	}
}
	
?>

<div class="container back">
<div class="row">
    <div class="col-md-8 col-md-offset-2 text-center">
      <h1 class="black">deserted place</h1>
      <h2 class="animated fadeInUp delay-05s black">Login pages</h2>
    </div>
</div>
<div class="window">
	<form method="post" class="form-signin" action="login.php">
		<div class="row">
			<h4 class="black">username:</h4><input type="text" class="form-control" name="user" >
		</div>
		<div class="row">
			<h4 class="black">password:</h4><input type="password" class="form-control"  name="pass" >
		</div>
		<input type="submit" style="display:inline;margin-left:130px" name="submit" value="login">
		<input type="button" class="register" style="display:inline; margin-left:50px" value="register">

	</form>
</div>

<?php
	require('class/footer.php');
?>
