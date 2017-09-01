<?php
header("X-XSS-Protection: 0");
header('Content-Disposition: attachment; filename="'.$_GET["filename"].'"');

if(substr($_GET["url"],0,4) ==="http" && substr($_GET["url"],0,8)<>"http://0" && substr($_GET["url"],0,8)<>"http://1" && substr($_GET["url"],0,8)<>"http://l" && strpos($_GET["url"], '@') === false)
{
$opts = array('http' =>
    array(
        'method' => 'GET',
        'max_redirects' => '0',
        'ignore_errors' => '1'
    )
);
$context = stream_context_create($opts);
$url=str_replace("..","",$_GET["url"]);
$stream = fopen($url, 'r', false, $context);
echo stream_get_contents($stream);
}
else
{
echo "Bad URL!";
}
?>

