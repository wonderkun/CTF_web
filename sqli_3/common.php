<?php

header("Content-Type:text/html;charset=utf-8");
define("DB_HOST", "127.0.0.1");
define("DB_USER", "root");
define("DB_PASS", "MSFADMIN@");
define("DB_NAME", "npuctf");
define("TB_PREFIX", "sqli_");

ini_set("display_errors", "On");
error_reporting(E_ALL);

session_start();
if(!isset($_SESSION["user"])){
    $_SESSION["user"] = md5(time() + rand(0, 9999));
}

?>