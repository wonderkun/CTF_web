FROM ubuntu:16.04
ENV DEBIAN_FRONTEND noninteractive
RUN   apt-get -y update && \
      apt-get -y install vim python sqlite3 python-pip&& \
      apt-get -y autoremove  && \
      rm -rf /var/lib/apt/lists/* 

COPY ./src /src  
COPY ./start.sh /start.sh
COPY ./pastebin.sql /tmp/pastebin.sql
COPY ./requirements.txt /tmp/requirements.txt
RUN chmod +x /start.sh

RUN /usr/bin/pip install --upgrade pip && \
    pip install flask && \
    pip install PyJWT && \
    sqlite3 /tmp/pastebin.db < /tmp/pastebin.sql && \
    pip install -r /tmp/requirements.txt && \
    rm /usr/local/lib/python2.7/dist-packages/jwt/algorithms.pyc

COPY ./algorithms.py  /usr/local/lib/python2.7/dist-packages/jwt/algorithms.py

WORKDIR /src/

EXPOSE 5000
CMD ["/start.sh"]