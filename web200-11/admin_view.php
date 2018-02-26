<?php
error_reporting(0);
session_start();
setcookie('yesiamadmin001','flag{this_is_flag}');
if(!$_COOKIE['yesiamadmin001'])
{
	exit('access denied');
}
if(!$_SESSION['nonce'])
{
	$_SESSION['nonce']=md5(rand(10000000,99999999));
}
$nonce=$_SESSION['nonce'];
?>
<html>
<meta http-equiv="Content-Security-Policy" content="script-src 'nonce-<?=$nonce;?>';">
<?php echo file_get_contents('loghehehaha.txt');?>
<p>comment here</p>
<script nonce="<?=$nonce;?>">var test='test';</script>
<p>welcome to comment on admin's blog</p>
</html>