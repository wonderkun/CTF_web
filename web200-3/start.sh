#!/bin/bash


service nginx restart
service php7.0-fpm start
service mysql restart  

# iptables 默认在docker中不允许运行的  需要加 --privileged=true 参数 
iptables   -t filter -A OUTPUT -p tcp --tcp-flags ALL SYN  -j DROP
iptables  -t filter  -A OUTPUT -p upd  -j DROP 


/usr/bin/tail -f /dev/null