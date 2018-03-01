<?php
require('../class/header.php');

if(!isset($_SESSION['user']) || $_SESSION['user']!='hctf_admin_LoRexxar2e23')
{
	exit("This is not where you should come, Hacker!!!");
}


$query = "SELECT * FROM `records` where `is_read` = 0 GROUP by id limit 0,1";
$result=$db->query($query);
$result_num=$result->num_rows;

if($result_num==0)
{
	exit("Nothing right now...sleep...");	
}
else
{
	$row = $result->fetch_assoc();
	$id = $row['id'];
	$link = $row['link'];

	$file  = 'l1nk10g2e23.log';
	$content = sprintf("time: %s , link: %s \r\n", date("h:i:sa"), $link);
	$f  = file_put_contents($file, $content,FILE_APPEND);


	$query = "UPDATE `records` SET `is_read` = '1' WHERE `records`.`id` = ".$id;
	$result = $db->query($query);

	if($result){
		header("location: ".$link);
	}else{
		exit("rediert error....");
	}
}

	
