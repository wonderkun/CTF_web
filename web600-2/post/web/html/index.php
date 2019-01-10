<?php
include "inc/bootstrap.php";
include "inc/header.php";

switch ($page) {
case "register":
    include "inc/register.php";
    break;
case "login":
    include "inc/login.php";
    break;
default:
    include "inc/default.php";
    break;
}

include "inc/footer.php";
