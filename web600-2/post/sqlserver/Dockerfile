FROM mcr.microsoft.com/mssql/server:2017-latest
ENV ACCEPT_EULA y
ENV SA_PASSWORD QIUHDI13hqssiuaQDHsaaseglpduac


ADD *.sql /tmp/

RUN /opt/mssql/bin/sqlservr --accept-eula & sleep 20 \
    && /opt/mssql-tools/bin/sqlcmd -S localhost -U SA -P "$SA_PASSWORD" -i /tmp/create_db.sql \
    && /opt/mssql-tools/bin/sqlcmd -S localhost -U SA -P "$SA_PASSWORD" -i /tmp/create_schema.sql \
    && /opt/mssql-tools/bin/sqlcmd -S localhost -U SA -P "$SA_PASSWORD" -i /tmp/create_tables.sql \
    && /bin/bash


