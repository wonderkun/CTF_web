FROM ubuntu:16.04 
MAINTAINER wonderkun <729173164@qq.com>
ENV DEBIAN_FRONTEND noninteractive 

RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list
RUN TZ=Asia/shanghai 
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update -y && \ 
    apt-get install -y nginx \
    php7.0-fpm \
    php-mysql  \ 
    mysql-client \
    mysql-server  \
    && /etc/init.d/mysql start \
    && mysqladmin -uroot password root  \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* 

# 拷贝文件 
WORKDIR  /tmp 
COPY ./start.sh /tmp/
RUN chmod a+x ./start.sh
# 设置数据库

RUN set -x \
    && service mysql start \ 
    && mysql  -e "CREATE DATABASE   ctf  DEFAULT CHARACTER SET latin1 ;"  -uroot  -proot \ 
    &&  mysql -e "grant select,create,insert on ctf.* to 'admin'@'localhost' identified by 'thisisApass' "  -uroot -proot  
    
# 复制源代码 
COPY ./default  /etc/nginx/sites-available/default
COPY ./src/    /usr/share/nginx/html/

# 修改目录权限

EXPOSE  80 3306 
CMD ["/tmp/start.sh"]
