#!/bin/bash
chown www-data:www-data /app -R

if [ "$ALLOW_OVERRIDE" = "**False**" ]; then
    unset ALLOW_OVERRIDE
else
    sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
    a2enmod rewrite
fi

# initialize database
mysqld_safe --skip-grant-tables&
sleep 5
## change root password
mysql -uroot -e "use mysql;UPDATE user SET password=PASSWORD('Nu1Lctf%#~:p') WHERE user='root';FLUSH PRIVILEGES;"
## restart mysql
service mysql restart
## execute sql file
mysql -uroot -pNu1Lctf\%\#\~\:p < /tmp/sql.sql

## crontab
(while true;do rm -rf /tmp/*;sleep 2;done)&

## rm sql
cd /tmp/
rm sql.sql

source /etc/apache2/envvars
tail -F /var/log/apache2/* &
exec apache2 -D FOREGROUND
