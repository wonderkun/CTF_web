FROM node:8.15-jessie

RUN mkdir /var/opt/node && \ 
    apt-get update && \
    apt-get install -y cowsay && \
    ln -s /usr/games/cowsay /usr/local/bin/cowsay
    
COPY  ./src /var/opt/node/
COPY ./package.json /var/opt/node/

WORKDIR /var/opt/node/
RUN npm install

EXPOSE 3000

CMD ["node","server.js"]