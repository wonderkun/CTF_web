<?php
header("X-XSS-Protection: 0");
$page=strtolower($_GET["page"]);
$regex="/on([a-zA-Z])+/i";
$page=str_replace("style","_",$page);
?>
<html>
<head>
<meta charset=utf-8>
</head>
<body>
<form action='xss15.php?page=<?php
if(preg_match($regex,$page))
{
echo "XSS Detected!";
}
else
{
echo htmlspecialchars($page);
}
?>'></form>
<script>
if(top!=self){
 top.location=self.location
}
</script>
</body>
</html>
