FROM 0kami/web:apache2_php5_auto

MAINTAINER wh1t3P1g <https://github.com/0kami>

COPY source /var/www/html
COPY flag /etc

RUN chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/runtime \
    && chmod -R 777 /var/www/html/public \
    && chmod 777 /var/www/html/application/database.php \
    && rm /var/www/html/index.html