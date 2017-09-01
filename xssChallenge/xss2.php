<?php
header('Pragma: cache');
header("Cache-Control: max-age=".(60*60*24*100)); 
header("X-XSS-Protection: 0");
?>
<html>
<head>
<meta charset=utf-8>
<head>
<body>

<?php
if(isset($_SERVER['HTTP_REFERER'])) 
{
echo "Bad Referrer!";
}
else
{
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
}
?>

</body>
</html>