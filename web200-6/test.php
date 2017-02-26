<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "123456";
$db = "ctf";
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$db);
mysqli_set_charset($conn,"utf8");

$sql = "select * from admin "
