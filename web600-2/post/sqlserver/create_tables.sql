USE challenge;

CREATE TABLE [challenge].[user] ( uid INT IDENTITY(1,1) PRIMARY KEY, username VARCHAR(255) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL); 

CREATE TABLE [challenge].posts ( id INT IDENTITY(1,1) NOT NULL PRIMARY KEY, attachment VARCHAR(4096) NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(4096) NOT NULL, userid INT FOREIGN KEY REFERENCES [challenge].[user](uid));

CREATE TABLE [flag].[flag] (flag VARCHAR(255));
INSERT INTO [flag].[flag] (flag) VALUES("35c3_wel1_job_good_d0ne_heyho");
