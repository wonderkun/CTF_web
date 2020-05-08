FROM ubuntu:18.04

RUN sed -i "s/http:\/\/archive.ubuntu.com/http:\/\/mirrors.tuna.tsinghua.edu.cn/g" /etc/apt/sources.list
RUN apt-get update && apt-get -y dist-upgrade
RUN apt-get -y install vim
RUN apt-get -y install tzdata
RUN apt-get -y install php
RUN apt-get -y install apache2
RUN apt-get -y install libapache2-mod-php
RUN apt-get install -y wget nginx gdb git unzip

RUN set -xe \
    && git clone https://github.com/longld/peda.git   ~/peda \
    && git clone https://github.com/scwuaptx/Pwngdb.git  ~/Pwngdb \ 
    && cp ~/Pwngdb/.gdbinit ~/

COPY ./Minclude.so /usr/lib/php/20170718/Minclude.so
RUN chmod 755 /usr/lib/php/20170718/Minclude.so
RUN rm /var/www/html/index.html
COPY index.php /var/www/html/index.php
RUN chmod 755 -R /var/www/html/
COPY flag /flag
RUN chmod 755 /flag
COPY ./php.ini /etc/php/7.2/apache2/php.ini
RUN chmod 755 /etc/php/7.2/apache2/php.ini
RUN echo "" > /etc/php/7.2/apache2/conf.d/20-json.ini


EXPOSE 80

CMD apachectl -X & tail -F /var/log/apache2/access.log

#CMD service apache2 start & tail -F /var/log/apache2/access.log
# docker run -it -d -p8088:80 --privileged 5e2dceb47231