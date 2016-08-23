<?php

session_start();

require("./class.php");

$f3 = new foo3();

$f3->varr = "phpinfo();";

$f3->execute();

?>