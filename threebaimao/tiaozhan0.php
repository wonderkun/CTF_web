
<?php

/*

   create table   baimaozi (intro  varchar(40),name varchar(20),nick varchar(20)); 
   insert  into baimaozi  values (md5('flag'),'wonderkun','wonderkun');
   
   create table `flag` (`flag` varchar(32));
   insert into flag values (md5('flag'));
      
*/

function sanitize($input){
    $blacklist = array('\'', '"', '/', '*', '.');
    return str_replace($blacklist, '', $input);
}


$host = "localhost";
$user = "root";
$pass = "i0ve*ctf";
$db = "sangebaimao";

$connect = mysql_connect($host, $user, $pass) or die("Unable to connect");
mysql_select_db($db) or die("Unable to select database");

$name = isset($_GET['name'])?sanitize($_GET['name']):die();

$query = 'select intro from baimaozi  where name=\''.$name.'\' or nick=\''.$name.'\' limit 1';

echo $query."</br>";


if (preg_match('/[^a-zA-Z0-9_]union[^a-zA-Z0-9_]/i', $name) || preg_match('/^union[^a-zA-Z0-9_]/i', $name)){
    echo "not allow";
    exit;
}

$result = mysql_query($query);
echo mysql_error();

$row = mysql_fetch_array($result);
echo $row[0];
