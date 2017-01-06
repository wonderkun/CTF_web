FROM ubuntu:16.04 

MAINTAINER wonderkun <729173164@qq.com>

RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list
RUN TZ=Asia/shanghai 
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update -y && \ 
    apt-get install -y iptables \
    nginx \
    php7.0-fpm  

RUN echo "mysql-server-5.5 mysql-server/root_password password this_is_amima" | debconf-set-selections \
    && echo "mysql-server-5.5 mysql-server/root_password_again password this_is_amima" | debconf-set-selections \
    && apt-get -y install mysql-server mysql-client  \
    && /etc/init.d/mysql start  \
    && rm -rf /var/lib/apt/lists/* 

COPY  ./default /etc/nginx/sites-available/default
COPY ./src /usr/share/nginx/html/
RUN chown -R www-data:www-data /usr/share/nginx/html/ 


WORKDIR /tmp  
COPY  ./start.sh  /tmp/

RUN  chmod a+x start.sh 
EXPOSE 80 3306 

CMD ["/tmp/start.sh"]

