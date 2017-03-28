<?php 
include_once "config.php";
require_once 'htmlpurifier/library/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'a,b,i,p,h1,h2,h3,h4,h5,h6,center,hr,br,div,span');
$purifier = new HTMLPurifier($config);


if(!$_SESSION['username']){
	Header("Location: login.php");
}
$stmt = $conn->prepare('SELECT id,`from`,`to`,`msg`,`time` FROM  message WHERE `to` = ? and `read` = false');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$head= <<<head
<div>
<table>
<thead>
<tr>
<td>id</td><td>from</td><td>to</td><td>msg</td><td>time</td>
</tr>
</thead>
<tbody id='msg_body'>
head;
$body=<<<body
</tbody>
</table>
</div>
body;
if($_SERVER['HTTP_X_REQUESTED_WITH']!='XMLHttpRequest'){
	include_once "header.php";
	echo $head;
	echo $body;
	if($_SESSION['isAdmin']){
		echo "<script>refresh(100)</script>";
	}	
}
while ($row = $result->fetch_assoc()) {
	$from=$row['from'];		
	$thisfrom=$from;
	$id=$row['id'];	
	$to=$row['to'];
	$msg=$row['msg'];	
	$time=$row['time'];
	$_msg = $purifier->purify($msg);
	$msg=str_ireplace("script","",$msg);
	$msg=str_ireplace("script","",$msg);
	$_from=$from;
	$_to=$to;
	$stmt = $conn->prepare("UPDATE  message set `read`=true WHERE  id=?");		
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$stmt->close();
	if($_SERVER['HTTP_X_REQUESTED_WITH']!='XMLHttpRequest'){
		myexit("displaymsg","<tr><td>$id</td><td><!-- $from -->$_from</td><td><!-- $to -->$_to</td><td><!-- $msg -->$_msg</td><td>$time</td>");	
	}else{
		myexit($callback,"<tr><td>$id</td><td><!-- $from -->$_from</td><td><!-- $to -->$_to</td><td><!-- $msg -->$_msg</td><td>$time</td>");	
	}
}


