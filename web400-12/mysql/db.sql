CREATE DATABASE `hardphp` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
use `hardphp`;

drop table if exists `dbsession`;
create table `dbsession` (
   `id` int(32) not null primary key auto_increment,
   `sessionid` varchar(60) not null,
   `data` text not null,
   `lastvisit` int(32) not null       
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
drop table if exists  `user`; 
create table `user` (
    `id` int(32) primary key auto_increment,
    `username` varchar(200) not null,
    `password` varchar(40) not null,
    `picture` text not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
drop table if exists `msg`;
create table `msg` (
    `id` int(32) primary key  auto_increment,
    `userid` int(32) not null,
    `content` text not null
)