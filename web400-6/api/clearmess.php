<?php
require('../class/header.php');

if(!isset($_SESSION['user']) && $_SESSION['user']='hctf_admin_LoRexxar2e23')
{
	exit("This is not where you should come, Hacker!!!");
}

$query = "UPDATE `users` SET `email`=NULL,`message`=NULL WHERE id = 1";
$result=$db->query($query);

if($result){
	exit("delete success");
}else{
	exit("ga li gg");
}