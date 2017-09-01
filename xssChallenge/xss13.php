<?php
header("X-XSS-Protection: 0");
echo "REQUEST_URI:".$_SERVER['REQUEST_URI'];
?>