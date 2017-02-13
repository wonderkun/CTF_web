
CREATE DATABASE `taolu` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
use `taolu`;

drop table if exists `user`;
create table `user`(
    `id` int(11) not null primary  key  auto_increment,
    `uname` varchar(20) not null,
    `password` varchar(32) not null,
    `level` tinyint not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop  table if  exists `note`;
create table `note` (

     `id` int(11) not null primary key auto_increment,
     `content` varchar(255) not null,
     `title`  varchar(255)  not null,
     `userid`  int(11) not null 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;


drop table  if exists `page` ;

create table `page` (
    `num` varchar(255)  not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table if exists `flags`;

create table `flags` (
      `id` tinyint not null primary key ,
      `flag` varchar(50) not null 
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into `flags` (id,flag) values (1,'CTF{945ad55928c3e9773c7y3cf2cf02cdbd}');
insert into  `user`(id,uname,password,level)values(
     1,'admin','21232f297a57a5a743894aoe4a801fc3',1
),(2,'test','202cb962ac59075b964b07152d234b70',0);

insert into  `page` values (20);
insert into   `note` (title,content,userid)values(
     '测试笔记','这是管理员发布的测试笔记,个人无法删除(hint:./dbinit.sql)',1
)