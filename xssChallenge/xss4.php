<?php
header("X-XSS-Protection: 0");
?>
<html>
<head>
<meta charset="utf-8">
</head>
<body>
<?php echo "你来自:".$_SERVER['HTTP_REFERER'];?>
</body>
</html>