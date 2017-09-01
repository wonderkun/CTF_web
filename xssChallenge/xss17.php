<?php
header("Content-Type:text/html;charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-FRAME-OPTIONS: DENY");
header("X-XSS-Protection: 0");
$content=$_GET["content"];
echo "<div data-content='".htmlspecialchars($content)."'>";
?>