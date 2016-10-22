<?php 
# GOAL: overwrite password for admin (id=1)
#       Try to login as admin
# $yourInfo=array( //this is your user data in the db
#   'id'    => 8,
#   'name'  => 'jimbo18714',
#   'pass'  => 'MAYBECHANGED',
#   'level' => 1
# );
require 'db.inc.php';
   
function mres($str) {
    return mysql_real_escape_string($str);
}
   
$userInfo = @unserialize($_GET['userInfo']);
   
$query = 'SELECT * FROM users WHERE id = \''.mres($userInfo['id']).'\' AND pass = \''.mres($userInfo['pass']).'\';';
   
$result = mysql_query($query);
if(!$result || mysql_num_rows($result) < 1){
    die('Invalid password!');
}
   
$row = mysql_fetch_assoc($result);
foreach($row as $key => $value){
    $userInfo[$key] = $value;
}
   
$oldPass = @$_GET['oldPass'];
$newPass = @$_GET['newPass'];
if($oldPass == $userInfo['pass']){
    $userInfo['pass'] = $newPass;
    $query = 'UPDATE users SET pass = \''.mres($newPass).'\' WHERE id = \''.mres($userInfo['id']).'\';';
    mysql_query($query);
    echo 'Password Changed.';
}
else{
    echo 'Invalid old password entered.';
}


