CREATE USER 'Nu1L'@'localhost' IDENTIFIED BY 'Nu1Lpassword233334';
create database nu1lctf;
use nu1lctf;

create table `ctf_users` (
    `id` int(32) auto_increment primary key,
    `username` varchar(40) not null,
    `password` varchar(32) not null,
    `ip` varchar(50) not null,
    `is_admin` int(1) not null default 0,
    `allow_diff_ip` int(1) not null default 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table `ctf_user_signature`(
   `id` int(32) auto_increment  primary key,
   `userid` int(32) not null,
   `username` varchar(40) not null,
   `signature` text not null,
   `mood` text not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into `ctf_users`(`username`,`password`,`ip`,`is_admin`,`allow_diff_ip`) value(
    "admin","2533f492a796a3227b0c6f91d102cc36",'127.0.0.1',1,0
);

grant all privileges  on nu1lctf.* to 'Nu1L'@'localhost' identified by 'Nu1Lpassword233334';