<?php
require('class/header.php');

if(!isset($_SESSION['user']))
	{
		echo "<script>alert('you need login first!')</script>";
		echo "<script>window.location.href='./index.php'</script>";
		exit;	
	}


$user = $_SESSION['user'];
$req = select($user);


if(empty($req['email'])){
	$email = 'None';
}else{
	$email = $req['email'];
}

if(empty($req['message'])){
	$message = 'None';
}else{
	$message = $req['message'];
}

$csrftoken = substr(md5($user.createRandomStr(8)),4,8);
$_SESSION['csrftoken'] = $csrftoken;

?>

<div class='col-md-8 col-md-offset-2 text-center head' id="head">
<h1>the deserted place</h1>
</div>

<div id='hide' class='col-md-8 col-md-offset-2 text-center'><h2 class='animated fadeInUp delay-05s white'>Welcome to deserted place</h2></div>

<div class="container back">

<?php


print <<<EOT

	<div class="list-group-item warn">
		<h3>
		Tips:
		</h3>
		<p>
		Hello {$user}, Welcome to the deserted place, there's nothing here, and try to find something. And if you find some bug, you can <a href="./report.php" style="margin-left:0px">report bug</a> to admin.
		</p>
	</div>
EOT;


?>
<div class="list-group-item main">

<div class="list" style="float:left">
	<ul class='list-group'>
		<li class="list-group-item" id="user">Username: <?=htmlspecialchars($user)?></li>
		<li class="list-group-item" id="email">Email: <?=$email?></li>
		<li class="list-group-item" id="mess">Message: <?=$message?></li>
		<li class="list-group-item" id="csrft" style="display: none">csrftoken: <?=$csrftoken?></li>
	</ul>
</div>

<div class="cimg" style="float:left">
	<div class="bimg">
		<img src="./static/e.png" onclick=edit()>
		<h4>change something</h4>
	</div>
	<div class="bimg">
		<img src="./static/c.png" onclick=random()>
		<h4>click me</h4>
	</div>
</div>


<?php
$db->close();

require('class/footer.php');
?>

