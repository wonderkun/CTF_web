<?php
header("Content-Type:text/html;charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-FRAME-OPTIONS: DENY");
header("X-XSS-Protection: 0");

$hookid=str_replace("=","",htmlspecialchars($_GET["hookid"]));
$hookid=str_replace(")","",$hookid);
$hookid=str_replace("(","",$hookid);
$hookid=str_replace("`","",$hookid);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<script>
hookid='<?php echo $hookid;?>';
</script>
<body>
</body>
</html>