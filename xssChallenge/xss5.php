<?php
header("X-XSS-Protection: 0");
$url=str_replace(urldecode("%00"),"",$_GET["url"]);
$url=str_replace(urldecode("%0d"),"",$url);
$url=str_replace(urldecode("%0a"),"",$url);
header("Location: ".$url);
?>
<html>
<head>
<meta charset="utf-8">
</head>
<body>
<?php echo "<a href='".$url."'>如果跳转失败请点我</a>";?>
</body>
</html>
