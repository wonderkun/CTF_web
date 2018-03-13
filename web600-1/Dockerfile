FROM andreisamuilik/php5.5.9-apache2.4-mysql5.5

ADD nu1lctf.tar.gz /app/
RUN apt-get update
RUN a2enmod rewrite
COPY sql.sql /tmp/sql.sql
COPY run.sh /run.sh
RUN mkdir /home/nu1lctf
COPY clean_danger.sh /home/nu1lctf/clean_danger.sh

RUN chmod +x /run.sh
RUN chmod 777 /tmp/sql.sql
RUN chmod 555 /home/nu1lctf/clean_danger.sh

EXPOSE 80
CMD ["/run.sh"]

