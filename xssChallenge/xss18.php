<?php
header("Content-Type:text/html;charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-FRAME-OPTIONS: DENY");
header("X-XSS-Protection: 1");
?>
<html>
<head>
<meta charset=utf-8>
</head>
<body>
<textarea>
<?php
//Fix#001
$input=str_replace("<script>","",$_GET["input"]);
//Fix#002
$input=str_replace("/","\/",$input);
echo $input;
?>

</textarea>
</body>
</html>
