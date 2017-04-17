<?php

require_once('key.php');
require_once('db.php');
require_once('encrypt.php');
error_reporting(0);

function register($user, $pass) {
	$user = '0x' . bin2hex($user);
	$pass = '0x' . bin2hex($pass);
	$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
	$result = mysqli_query($con, "select * from user where user=$user");
	$data = mysqli_fetch_assoc($result);
	if ($data) return false;
	return mysqli_query($con, "insert into user (user,pass) values ($user,$pass)");	
}

function login($user, $pass) {
	$user = '0x' . bin2hex($user);
	$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
	$result = mysqli_query($con, "select * from user where user=$user");
	$data = mysqli_fetch_assoc($result);
	if (!$data) return false;
	if ($data['pass'] === $pass) return true;
	return false;
}

$token = '';
$user = '';
$admin = 0;
if (isset($_COOKIE['token'])&&isset($_COOKIE['sign'])) {
	$sign = $_COOKIE['sign'];
	$token = $_COOKIE['token'];
	$arr = explode('|', token_decrypt($token));

	if (count($arr) == 3) {
		if (md5(get_indentify().$arr[0]) === $arr[2] && $sign === $arr[2]) {
			$user = $arr[0];
			$admin = (int)$arr[1];
		}
	}
}
// die();

if (isset($_GET['action'])) {
	$action = $_GET['action'];
}else {
	header("HTTP/1.1 302 Found");
	header("Location: ?action=home");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html class="no-js" lang="en"> 
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>登录</title>
  <link rel="stylesheet" href="./css/main.css">
  </head>
<body>
<div class="row">

	<div class="four columns centered">
	     <?php if ($user) { ?>
		<a href="?action=home">Home</a>
		<a href="?action=manage">Manage</a>
		<a href="?action=loginout">Logout</a>

		<br/>User:<?php echo $user; ?><br />
		<?php } else { ?>
		<a href="?action=home">Home</a>
		<a href="?action=login">Login</a>
		<a href="?action=register">Register</a>
		<?php } ?>

        <?php
    switch ($action) {
    case 'loginout':
	    setcookie('sign',"",time()-1,"/",'',false,true);
	    setcookie('token',"",time()-1,"/",'',false,true);
		header("HTTP/1.1 302 Found");
		header("Location: ?action=login");
	case 'login':
	if ($user) {
		header("HTTP/1.1 302 Found");
		header("Location: ?action=home");
	}elseif(isset($_POST['user']) && isset($_POST['pwd'])) {
			if ($_POST['user'] == '') echo 'Username Required';
			elseif ($_POST['pwd'] == '') echo 'Password Required';
			elseif (!login((string)$_POST['user'], (string)$_POST['pwd'])) echo 'Incorrect';
			else {
				$user = $_POST['user'];
				// get_indentify() 获取10位的key,做一个身份签名,防止身份伪造
				
				$md5 = md5(get_indentify().$user);
				$admin = 0;
				// $token = token_encrypt("$user|$admin|$md5");
				$token = token_encrypt("$user|$admin|$md5");
				setcookie('sign',$md5,time()+5*60,"/",'',false,true);
				setcookie('token',$token,time()+5*60,"/",'',false,true);
				header("HTTP/1.1 302 Found");
				header("Location: ?action=home");
			}
		}
		?>
		<form method="POST" action="index.php?action=login">
			<fieldset>
				<legend>Login</legend>
				<p>Username: <input type="text" name="user" id="username" size="25" required/></p>
				<p>Password: <input type="password" name="pwd" id="passwd" size="25" required/></p>
				<p><input type="submit" class="small button" name="submit" id="submit" value="Submit"/></p>
			</fieldset>
		</form>

		<?php
		break;
	case 'register':
		if ($user) {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=home");
		}elseif (isset($_POST['user']) && isset($_POST['pwd'])) {
			if ($_POST['user'] == '') echo 'Username Required';
			elseif ($_POST['pwd'] == '') echo 'Password Required';
			elseif (!register((string)$_POST['user'], (string)$_POST['pwd'])) echo '<script>alert("User Already Exists");</script>';
			else  echo '<script>alert("OK")</script>';
		}
		?>
		<form method="POST" action="index.php?action=register">
			<fieldset>
				<legend>Register</legend>
				<p>Username: <input type="text" name="user" id="username" size="25" required/></p>
				<p>Password: <input type="password" name="pwd" id="passwd" size="25" required/></p>
				<p><input type="submit" class="small button" name="submit" id="submit" value="Submit"/></p>
			</fieldset>
		</form>

		<?php
		break;
		case 'manage':
		if ($user) {
			if ($admin) {
				$text = '';
				if (isset($_POST['do'])) {
					switch ($_POST['do']) {
						case 'encrypt':
							$text = bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, get_key(), hex2bin($_POST['text']), MCRYPT_MODE_CFB, hex2bin($_POST['iv'])));
							break;
						case 'decrypt':
							$text = bin2hex(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, get_key(), hex2bin($_POST['text']), MCRYPT_MODE_CFB, hex2bin($_POST['iv'])));
							break;
					}
				}
				?>
				<h2>Secret Area</h2>
				<?php echo $text; ?><br />
				<form action="?action=manage" method="post">
				<input type="text" name="text" placeholder="text" /><br />
				<input type="text" name="iv" placeholder="iv" /><br />
				<input type="submit" name="do" value="encrypt" />
				<input type="submit" name="do" value="decrypt" />
				<br/>
				</form>
				<?php
			}
			else {
				echo 'You are not admin';
			}
		}
		else {
			header("HTTP/1.1 302 Found");
			header("Location: ?action=login");
		}
		break;
	case 'home':
		?>
		<h4>welcome to my game!</h4>
		<?php
		break;
   }
?>
</div>
<div class="body" align="center">
	</div><br/><br/><br/>
</div>
</body>
</html>