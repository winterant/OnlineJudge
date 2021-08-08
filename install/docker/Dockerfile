FROM ubuntu:20.04

COPY LDUOnlineJudge/ /home/LDUOnlineJudge/

RUN set -ex &&\
    #cat /home/LDUOnlineJudge/install/docker/sources.list > /etc/apt/sources.list &&\
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone

RUN bash /home/LDUOnlineJudge/install/ubuntu/install.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
