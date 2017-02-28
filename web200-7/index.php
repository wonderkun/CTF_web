<?php
if(!isset($_GET['option'])) die();
$str = addslashes($_GET['option']);
// echo $str;

$file = file_get_contents('./config.php');
$file = preg_replace('|\$option=\'.*\';|', "\$option='$str';", $file);
file_put_contents('./config.php', $file);
