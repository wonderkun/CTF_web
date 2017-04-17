FROM ubuntu:16.04 

MAINTAINER wonderkun <729173164@qq.com>
ENV DEBIAN_FRONTEND noninteractive 

RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list
RUN TZ=Asia/shanghai 
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update -y && \ 
    apt-get install -y nginx \
    vim \
    tar \
    php7.0-fpm \
    php7.0-mcrypt \ 
    php-mysql  \ 
    mysql-client \
    mysql-server   \
    && /etc/init.d/mysql start \
    && mysqladmin -uroot password root  \
    && rm -rf /var/lib/apt/lists/* 

WORKDIR /tmp  
COPY  ./start.sh  /tmp/
COPY  ./init.sql  /tmp/ 
RUN  chmod a+x start.sh 

#设置数据库 
RUN set -x \
    && service mysql start \ 
    && mysql  -e "CREATE DATABASE  blog  DEFAULT CHARACTER SET utf8;"  -uroot  -proot \ 
    &&  mysql -e "grant select,insert on blog.* to 'admin'@'localhost' identified by 'password' "  -uroot -proot   \ 
    &&  mysql -e "use blog;source /tmp/init.sql;"  -uroot -proot \
    && rm /tmp/init.sql 

# copy 源码
COPY  ./default /etc/nginx/sites-available/default
COPY ./src /usr/share/nginx/html/

# 设置可写权限 
RUN chown -R  www-data:www-data /usr/share/nginx/html/ 
EXPOSE 80 3306 

CMD ["/tmp/start.sh"]