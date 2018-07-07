FROM ubuntu:16.04
MAINTAINER cl0und "cl0und@sycl0ver"
RUN apt-get update && apt-get install -y vsftpd
RUN apt-get install -y vim
RUN apt-get install -y curl
COPY vsftpd /etc/init.d/vsftpd
RUN chmod 755 /etc/init.d/vsftpd
RUN chown root:root /etc/init.d/vsftpd
RUN useradd -m -d /home/syc10ver -s /bin/bash syc10ver 
RUN echo 'syc10ver:Eec5TN9fruOOTp2G' | chpasswd
RUN echo 'root:W8cjifzTASLXBdYf' | chpasswd
RUN echo sctf{Not_0n1y_xx3_but_als0_web_cache}>>/home/syc10ver/flag327a6c4304ad5938eaf0efb6cc3e53dc