FROM ubuntu:16.04
MAINTAINER cl0und "cl0und@sycl0ver"
RUN apt-get update && apt-get install -y nginx
RUN apt-get install -y vim
RUN apt-get install -y curl
ADD nginx/nginx.conf /etc/nginx/nginx.conf
RUN chmod 744 /etc/nginx/nginx.conf
ADD nginx/favicon.ico /home/