<?php 

include "flag2.php";
error_reporting(0);
show_source(__FILE__);

$a = @$_REQUEST['hello'];
eval("var_dump($a);");
