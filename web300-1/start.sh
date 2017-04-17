#!/bin/bash


service nginx restart
service php7.0-fpm start
service mysql restart 



# 设置配置文件  
echo -n 'admin' > /etc/db-user 
echo -n 'password' > /etc/db-pass 
echo -n '9418b4d829B9ec9F' > /etc/key
echo -n 'tl08hPeVdO' > /etc/indentify 

# 设置备份文件

cd /usr/share/nginx/html/
php ./backup_old.php
rm ./flag.txt 
tar -zcvf www.tar.gz  .

/usr/bin/tail -f /dev/null