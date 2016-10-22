<?php 

# GOAL: dump the info for the secret id
require 'db.inc.php';
   
$id = @(float)$_GET['id'];
   
$secretId = 1;
if($id == $secretId){
    echo 'Invalid id ('.$id.').';
}
else{
    $query = 'SELECT * FROM users WHERE id = \''.$id.'\';';
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
   
    echo "id: ".$row['id']."</br>";
    echo "name:".$row['name']."</br>";
}

//http://php4fun.sinaapp.com/c3/index.php?id=1.0000000000001  
