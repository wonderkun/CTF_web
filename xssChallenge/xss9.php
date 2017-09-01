<?php
header("X-XSS-Protection: 0");
header("Content-Type: text/html;charset=gb3212");
?>
<plaintext><?php echo $_GET["text"];?>
