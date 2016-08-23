#注意数据库权限限制

drop table if exists sqli_note;
CREATE TABLE `sqli_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) DEFAULT NULL,
  `user` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `isdeleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8


insert into sqli_note (title, content, user) values ("测试笔记", "欢迎使用西北工业大学信息安全协会云笔记系统", "root");

drop table if exists sqli_flag;

create table `sqli_flag` (
    'flag' varchar(50)
)ENGINE=InnoDB DEFAULT CHARSET=utf8

insert into flag values("npusec-ctf{593e2df4bf5b1c0ef846bc72677a1277}");

