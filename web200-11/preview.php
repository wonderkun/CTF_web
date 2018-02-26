<?php
error_reporting(0);
session_start();
header("X-XSS-Protection:0");
if(!$_SESSION['nonce'])
{
	$_SESSION['nonce']=md5(rand(10000000,99999999));
}
$nonce=$_SESSION['nonce'];
?>
<html>
<meta http-equiv="Content-Security-Policy" content="script-src 'nonce-<?=$nonce;?>';">
<?php echo $_POST['content'];?>
<p>comment here</p>
<script nonce="<?=$nonce;?>">var test='test';</script>
<p>welcome to comment on admin's blog</p>
</html>