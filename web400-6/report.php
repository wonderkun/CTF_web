<?php
require('class/header.php');

if(!isset($_SESSION['user']))
{
	echo "<script>alert('you need login first!')</script>";
	echo "<script>window.location.href='./index.php'</script>";
	exit;	
}

$user = $_SESSION['user'];

function GetIP(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif(!empty($_SERVER["REMOTE_ADDR"])){
		$cip = $_SERVER["REMOTE_ADDR"];
	}
	else{
		$cip = "NULL";
	}
	 return $cip;
}

if(!empty($_POST['user']) && !empty($_POST['link']) && !empty($_POST['code'])){

	$name = trim($_POST['user']);
	$link = trim($_POST['link']);
	$code = trim($_POST['code']);
	
	if(substr(md5($code),0,6) != $_SESSION['captcha']){
		echo "<script>alert('Verification Code error...')</script>";
		echo "<script>window.location.href='./report.php'</script>";
		exit;
	}

	$captcha=substr(md5(rand(1000000,9999999)),0,6);
	$_SESSION['captcha']=$captcha;

	if(!get_magic_quotes_gpc()) { 
        $name = addslashes($name);
        $link = addslashes($link);
	}

	if((strcasecmp(substr($link,0,4), "http")) != 0){
		echo "<script>alert('Only http protocols...')</script>";
		echo "<script>window.location.href='./report.php'</script>";
		exit;
	}


	$query = "insert into records (link) values ('{$link}')";
	$result = $db->query($query);

	if($result){
		$file  = 'it51zlog_link23.log';
		$content = sprintf("ip: %s , link: %s \r\n", GetIP(), $link);
		$f  = file_put_contents($file, $content,FILE_APPEND);


		echo "<script>alert('report success...')</script>";
		echo "<script>window.location.href='./user.php'</script>";
		exit;
	}else{
		echo "<script>alert('database error, please Contact administrator...')</script>";
	}
}

$captcha=substr(md5(rand(1000000,9999999)),0,6);
$_SESSION['captcha']=$captcha;


?>


<div class='col-md-8 col-md-offset-2 text-center head' id="head">
<h1>the deserted place</h1>
</div>

<div id='hide' class='col-md-8 col-md-offset-2 text-center'><h2 class='animated fadeInUp delay-05s white'>report bug</h2></div>


<div class="container back">

<div class="window">
<form method="post" class="form-signin" action="report.php">
	<div class="row">
	<h4 class="black">username:</h4><input type="text" class="form-control" id="user" name="user" readonly="readonly" value="<?=$user?>">
	</div>
	<div class="row">
	<h4 class="black">bug link:</h4><input type="text" class="form-control" name="link">
	</div>
	<div class="row">
	<h4 class="black">substr(md5($code),0,6) == '<?=$captcha?>'</h4><input type="text" class="form-control" name="code">
	</div>
	<input type="submit" value="submit">
</form>
</div>

</div>

<?php
	require("./class/footer.php");
?>
