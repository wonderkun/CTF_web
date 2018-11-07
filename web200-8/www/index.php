<?php
    highlight_file(__FILE__);
    error_reporting(0);
    ini_set('open_basedir', '/var/www/html:/tmp');
    $file = 'function.php';
    $func = isset($_GET['function'])?$_GET['function']:'filters'; 
    call_user_func($func,$_GET);
    include($file);
    session_start();
    $_SESSION['name'] = $_POST['name'];
    if($_SESSION['name']=='admin'){
        header('location:admin.php');
    }
?>
