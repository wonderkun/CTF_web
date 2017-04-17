#!/bin/bash


service nginx restart
service php7.0-fpm start
service mysql restart  


/usr/bin/tail -f /dev/null