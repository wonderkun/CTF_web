FROM ubuntu:16.04

RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list

RUN apt-get -y update && \ 
    apt-get install -y python python-dev python-pip 
COPY  ./src /src

WORKDIR /src
RUN pip install -r requirements.txt

EXPOSE 8080  

CMD ["python","app.py"]
