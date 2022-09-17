FROM --platform=linux/amd64 ubuntu:20.04

RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.163.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt-get update && apt-get -y upgrade &&\
    apt-get -y install software-properties-common &&\
    apt-get -y install language-pack-en-base &&\
    echo -e "\n" | add-apt-repository ppa:deadsnakes/ppa &&\
    apt-get update && apt-get -y upgrade &&\
    apt-get -y install mysql-client=8.0.* &&\
    apt-get -y install gcc g++ libmysqlclient-dev make flex openjdk-8-jre openjdk-8-jdk python3.8

COPY . /judge/

WORKDIR /judge

RUN cp -rf sim/sim.1 /usr/share/man/man1/ &&\
    cd sim/ && make install && cd ../ &&\
    chmod +x /judge/docker-entrypoint.sh

ENTRYPOINT ["/judge/docker-entrypoint.sh"]
