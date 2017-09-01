<?php
header("X-XSS-Protection: 0");
header("Content-Type: text/html;charset=utf-8");

if(substr($_GET["url"],0,4) ==="http" && substr($_GET["url"],0,8)<>"http://0" && substr($_GET["url"],0,8)<>"http://1" && substr($_GET["url"],0,8)<>"http://l" && strpos($_GET["url"], '@') === false)
{
$rule="/<[a-zA-Z]/";
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
$content=stream_get_contents($stream);
if(preg_match($rule,$content))
{
echo "XSS Detected!";
}
else
{
echo $content;
}
}
else
{
echo "Bad URL!";
}
?>
