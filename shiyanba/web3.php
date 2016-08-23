<?php

// $unserialize_str = $_POST['password']; $data_unserialize = unserialize($unserialize_str); if($data_unserialize['user'] == '???' && $data_unserialize['pass']=='???') { print_r($flag); }

$password=array(
    "user"=>TRUE,
    "pass"=>TRUE,
);
 
$password=serialize($password);
 

$data_unserialize = unserialize($password);

if ($data_unserialize['user']=="dsdsdds"){
    echo "sucess";
    
}

?>