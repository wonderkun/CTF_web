<?php 

# GOAL: get the key from $hidden_password[207]
   
session_start();
error_reporting(0);
   
function auth($password, $hidden_password) {
    $res = 0;
    if(isset($password) && $password != "") {
        if($password == $hidden_password) {
            $res = 1;
        }
    }
    $_SESSION["logged"] = $res;
    return $res;
}
   
function display($res){
    $aff = htmlentities($res);
    return $aff;
}
   
   
if(!isset($_SESSION["logged"]))
    $_SESSION["logged"] = 0;
   
$aff = "";
include("config.inc.php");
   
foreach($_REQUEST as $request) {
    if(is_array($request)) {
        die("Can not use Array in request!");
    }
}
   
$password = $_POST["password"];
   
if(!ini_get("register_globals")) {
    $superglobals = array($_POST, $_GET);
    if(isset($_SESSION)) {
        array_unshift($superglobals, $_SESSION);
    }
    foreach($superglobals as $superglobal) {
        extract($superglobal, 0);
    }
}
   
if((isset($password) && $password != "" && auth($password, $hidden_password[207]) == 1) || (is_array($_SESSION) && $_SESSION["logged"] == 1)) {
    $aff = display("$hidden_password[207]");
} else {
    $aff = display("Try again");
}
echo $aff;
