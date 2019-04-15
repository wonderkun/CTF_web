FROM postgres



COPY  ./schema.sql  /docker-entrypoint-initdb.d/
COPY ./postgresql.conf /etc/postgresql/postgresql.conf
RUN chmod 0777 /etc/postgresql/postgresql.conf && mkdir -p /var/lib/postgresql/data/triggered/static 