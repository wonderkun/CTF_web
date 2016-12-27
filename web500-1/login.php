<?php
    if(isset($_POST["username"]) && isset($_POST["password"])){
        session_start();
        $ip = $_SERVER["REMOTE_ADDR"];
        if($ip == "::1" || $ip == "127.0.0.1"){
            $_SESSION["token"] = "0";     //Administrator
            header("Location: index.php");
        }else{
            $key = $_POST["username"] . "~:" . $_POST["password"];
            $_SESSION["token"] = base64_encode($key);
            header("Location: index.php");
        }
    }else{
        header("Location: login.html");
        exit();
    }
?>
