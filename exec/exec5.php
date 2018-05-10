<?php 

$query = $_GET['query'];
// $query = '--open-files-in-pager=id;';
system('git grep -i --line-number '.escapeshellarg($query).' *');