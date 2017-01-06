
DROP TABLE if exists `users`;

create table `users` (

    `id` int(5) not null primary key auto_increment, 
    `username` varchar(20) not null, 
    `password` varchar(32) not null 

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREAte TABLE `flag`(

      `flag` varchar(40)

)

insert into `users` (id,username,password) values (1,'admin','fcb719025126a5a743d525dacd6724d4');  -- 设置此hash,需为无法破解的  
insert into   `flag` values ('flag{d15443798b35face458a88dc9561eaa7}') ;  -- 部署时重新设置flag  







