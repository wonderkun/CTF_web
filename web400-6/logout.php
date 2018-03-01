<?php
require('class/header.php');
session_destroy();
header("location: ./index.php");
?>