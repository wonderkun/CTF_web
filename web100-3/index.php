<?php
/*  
sql:
  
CREATE TABLE IF NOT EXISTS `client_ip` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `ip` varchar(200) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=34 ;
 
CREATE TABLE IF NOT EXISTS `flag` (
 `flag` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=gbk;
 
INSERT INTO `flag` (`flag`) VALUES
('cdbf14c9551d5be5612f7bb5d2867853');
  
*/


error_reporting(0);
 
function getIp(){
    $ip = '';
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
     $ip = $_SERVER['REMOTE_ADDR'];
}
   $ip_arr = explode(',', $ip);
   return $ip_arr[0];
   
}
 
$host="localhost";
$user="root";
$pass="i0ve*ctf";
$db="shiyanba";
 
$connect = mysql_connect($host, $user, $pass) or die("Unable to connect");
 
mysql_select_db($db) or die("Unable to select database");
 
$ip = getIp();
echo 'your ip is :'.$ip;
$sql="insert into client_ip (ip) values ('$ip')";
mysql_query($sql);
 
 
?>