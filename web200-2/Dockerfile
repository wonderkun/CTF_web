FROM  ubuntu:16.04
MAINTAINER wonderkun <729173164@qq.com>

RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Update sources
RUN apt-get update -y

RUN apt-get install -y  bash-completion unzip build-essential gcc g++ autoconf libiconv-hook-dev 

# nginx php
RUN apt-get install -y nginx php7.0-fpm  
RUN rm -rf /var/lib/apt/lists/*
 
COPY src/default /etc/nginx/sites-available/default
COPY src/index.php /usr/share/nginx/html/index.php
COPY src/flag.txt  /tmp/flag

RUN  mkdir /usr/share/nginx/html/upload/
RUN chown -R www-data:www-data /usr/share/nginx/html \
    && ln -s /usr/share/nginx/html /html
COPY src/start.sh /start.sh
RUN chmod a+x /start.sh

EXPOSE 80 
CMD ["/start.sh"]