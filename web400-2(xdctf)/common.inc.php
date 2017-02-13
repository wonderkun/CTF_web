<?php
/**
 * Created by PhpStorm.
 * User: phithon
 * Date: 15/10/14
 * Time: 下午7:58
 */

$DATABASE = array(
    
    "dsl" => "mysql:host=localhost;dbname=xdctf",
    "username" => "root",
    "password" => "root"

);

$db = new PDO($DATABASE["dsl"], $DATABASE["username"], $DATABASE["password"]);

$req = array();

foreach(array($_GET, $_POST, $_COOKIE) as $global_var) {
    foreach($global_var as $key => $value) {
        is_string($value) && $req[$key] = addslashes($value);
    }
}

define("UPLOAD_DIR", "upload/");

function redirect($location)
{
    header("Location: {$location}");
    exit;
}