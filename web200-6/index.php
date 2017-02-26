<?php
  $dbhost = "localhost";
  $dbuser = "root";
  $dbpass = "123456";
  $db = "ctf";
  $conn = mysqli_connect($dbhost,$dbuser,$dbpass,$db);
  mysqli_set_charset($conn,"utf8");

  /* sql

     create  table `admin` (
        `id` int(10) not null primary key auto_increment,
        `username` varchar(20) not null ,
        `password` varchar(32) not null
     );
  */
function   filter($str){
      $filterlist = "/\(|\)|username|password|where|case|when|like|regexp|into|limit|=|for|;/";
      if(preg_match($filterlist,strtolower($str))){
        die("illegal input!");
      }
      return $str;
  }
$username = isset($_POST['username'])?filter($_POST['username']):die("please input username!");
$password = isset($_POST['password'])?filter($_POST['password']):die("please input password!");
$sql = "select * from admin where  username = '$username' and password = '$password' ";
$res = $conn -> query($sql);

if($res->num_rows>0){
  $row = $res -> fetch_assoc();
  if($row['id']){
     echo $row['username'];
  }
}else{
   echo "The content in the password column is the flag!";
}

?>
