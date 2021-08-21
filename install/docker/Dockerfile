FROM ubuntu:20.04

COPY LDUOnlineJudge/ /volume/LDUOnlineJudge/

RUN set -ex &&\
    #cat /volume/LDUOnlineJudge/install/docker/sources.list > /etc/apt/sources.list &&\
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone

RUN bash /volume/LDUOnlineJudge/install/ubuntu/install.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
