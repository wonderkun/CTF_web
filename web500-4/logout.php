<?php
require_once "config.php";
?>
<?php
session_destroy();
exit("<script>location.href='index.php';</script>");