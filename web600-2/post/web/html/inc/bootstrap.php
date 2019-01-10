<?php
session_start();
include 'inc/db.php';
include 'inc/user.php';

$page = $_GET["page"] ?? "";

if (!isset($_SESSION["username"]) && !in_array($page, array("login","register"))) {
    header("Location: /?page=login");
    die;
} else if (isset($_SESSION["username"])) {
    $USER = new User($_SESSION["username"], $_SESSION["password"]);
    if (isset($_SERVER["HTTP_DEBUG"])) var_dump($USER);
}

