<?php
header("Content-Type:text/javascript; charset=utf-8");
header("X-XSS-Protection: 0");
echo $_GET["callback"].'({"q":"","p":false,"bs":"","csor":"0","status":769,"s":[]});';
?>
