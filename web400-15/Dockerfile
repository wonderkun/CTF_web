# echo 'hxp{FLAG}' > flag.txt && docker build -t includer . && docker run -ti -p 8004:80 includer
FROM debian:buster

RUN DEBIAN_FRONTEND=noninteractive apt-get update && \
    apt-get install -y \
        nginx \
        php-fpm \
    && rm -rf /var/lib/apt/lists/

RUN rm -rf /var/www/html/*
COPY docker-stuff/default /etc/nginx/sites-enabled/default
COPY docker-stuff/www.conf /etc/php/7.3/fpm/pool.d/www.conf

#  # Permission
#  7 rwx
#  6 rw-
#  5 r-x
#  4 r--
#  3 -wx
#  2 -w-
#  1 --x
#  0 ---

COPY flag.txt docker-stuff/readflag /
RUN chown 0:1337 /flag.txt /readflag && \
    chmod 040 /flag.txt && \
    chmod 2555 /readflag && \
    chmod 700 /tmp /var/lib/php/sessions


COPY index.php docker-stuff/security.txt /var/www/html/
RUN chown -R root:root /var/www && \
    find /var/www -type d -exec chmod 555 {} \; && \
    find /var/www -type f -exec chmod 444 {} \;  && \
    mkdir /var/www/html/files /var/www/html/well-known && \
    chmod 703 /var/www/html/files && \
    chmod 705 /var/www/html/well-known && \
    mv /var/www/html/security.txt /var/www/html/well-known

RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

USER www-data
RUN (find --version && id --version && sed --version && grep --version) > /dev/null
RUN ! find / -writable -or -user $(id -un) -or -group $(id -Gn|sed -e 's/ / -or -group  /g') 2> /dev/null | grep -Ev -m 1 '^(/dev/|/run/|/proc/|/sys/|/var/tmp|/var/lock|/var/log/nginx/error.log|/var/log/nginx/access.log|/var/www/html/files)'

USER 1337:1337
RUN ! find / -writable -or -user 1337 -or -group 1337 2> /dev/null | grep -Ev -m 1 '^(/dev/|/run/|/proc/|/sys/|/var/tmp|/var/lock|/var/log/nginx/error.log|/var/log/nginx/access.log|/var/www/html/files|/readflag|/flag.txt)'
USER root

EXPOSE 80
CMD while true; do find /var/www/html/files/ -maxdepth 1 -mindepth 1 -type d -mmin +15 -exec rm -rf -- {} \; ; sleep 1m; done & \
    /etc/init.d/php7.3-fpm start && \
    nginx -g 'daemon off;'
