CREATE TABLE user(
   id INTEGER PRIMARY KEY,
   username TEXT NOT NULL,
   password TEXT NOT NULL,
   priv TEXT NOT NULL,
   key TEXT NOT NULL,
   token TEXT NOT NULL
);

CREATE TABLE link(
   id INTEGER PRIMARY KEY,
   username TEXT NOT NULL,
   link TEXT NOT NULL,
   content TEXT NOT NULL
);


insert into `user` (`id`,`username`,`password`,`priv`,`key`,`token`) values ('1','admin','5lCQXkeRLA66Hwuw','admin','16c5cbfdd8bfa27187a4fff0ef8923fe','eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiYWRtaW4iLCJwcml2IjoiYWRtaW4ifQ.pW7evAq5E3O_34mwKq2ODBZX-pn4fsh-fh29IPhdBSJmmANv8m-ved0JjrN2Z0_WGkirLAyFzZWlBVo9CmB3y870kHXEQSh_HmlpSsEVCuhaSUaHYfm76hmniRJhHIi6lHHHF8GwNldYW5M2xO05_xc48XOEJ9SxryyZV2eQ8m4');
insert into `link`(`id`,`username`,`link`,`content`) values ('1','admin','22f1e0aa7a31422ad63480aa27711277','flag{this_is_flag}');