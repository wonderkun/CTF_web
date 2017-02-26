<?php

error_reporting(0);
session_start();
$servername = "localhost";
$username = "root";
$password = "root";
$database="taolu";

// 创建连接
$conn = mysqli_connect($servername, $username, $password,$database) or die(" connect to mysql error");
// $conn->query("set names 'utf8'");

$conn-> mysqli_set_charset("utf8");






