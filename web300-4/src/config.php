<?php 

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "123456";
$dbname = "web";
$flag = 'flag{this_is_flag}';
$defaultId = 'guest';
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);


/*  
create table `users` (
    `id` int(32) auto_increment primary key,
    `username` varchar(40) not null,
    `encrypted_pass` varchar(100) not null
);

*/