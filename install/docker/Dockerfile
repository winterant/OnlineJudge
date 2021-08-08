FROM ubuntu:16.04

COPY LDUOnlineJudge/ /home/LDUOnlineJudge/

RUN set -ex &&\
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    apt-get update &&\
    apt-get install -y apt-transport-https apt-utils &&\
    cat /home/LDUOnlineJudge/install/docker/sources.list > /etc/apt/sources.list

RUN bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
