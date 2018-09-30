<?php

$url1 = $_POST['url'];

$url_file = fopen("urlxxxx.txt", "a");
fwrite($url_file, $url1."\n");
fclose($url_file);
//$cookie1 = 'flag=green{c0nstructor.Constructor(al3rt(1))}';

?>