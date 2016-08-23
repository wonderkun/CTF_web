<?php



/*

数据库是npuctf； 

sql表 

create table if not exists sqlinject(
   id int not null ,
   username  varchar(20) not null,
   password  varchar(32)  not null
); 

insert  into sqlinject (
   id,username,password
) values (1,'fuck_range',md5('rangnicai1')),(2,'fuck_yichin',md5('rangnicai2')),(3,'rimutoren',md5('rangnicai3')); 


*/

// error_reporting(0);


$conf=array(
	'server'=>'localhost',
	'user'=>'root',
	'password'=>'i0ve*ctf',  //写自己的数据库密码,注意权限
	'DB'=>'npuctf'
	);

$conn=mysql_connect($conf['server'],$conf['user'],$conf['password']);

if(!$conn){
    
	die("mysql connect error");

}else{
    
mysql_select_db($conf['DB'],$conn);

}

$flag=md5('nwpuctf_is_wonderful');

?>