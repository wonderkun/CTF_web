<?php 

error_reporting(0);

$flag = "flag{e4d93a53bbe9a2f9c419086c16439aa7}";
$dbhost = "127.0.0.1";
$dbuser = "admin";
$dbpass = "thisisApass";
$dbname = "ctf";
$install = @$_POST["wonderkun_install_this_project!"];
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
