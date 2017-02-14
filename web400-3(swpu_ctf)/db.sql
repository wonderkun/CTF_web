

CREATE DATABASE `ctf`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

use `ctf`;

CREATE table `user` (
    `userid` int(11) not null primary key auto_increment,
    `name` varchar(20) not null ,
    `salt` varchar(40) not null ,
    `passwd` varchar(40) not null ,
    `check` varchar(40) not null ,
    `role` int(2) not null DEFAULT 0
)ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE table `msg`(
   `id` int(11) not null primary key auto_increment,
   `userid` int(11) not null,
   `msg` varchar(100) not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE table `flag`(
    `flag` varchar(40) not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into flag(`flag`) values ('flag{37316894c36cb32d2ca3f7d3add88024}');

--  admin 自己注册吧,吧role修改未1就行了
 
