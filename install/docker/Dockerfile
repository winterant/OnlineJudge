FROM ubuntu:20.04

COPY LDUOnlineJudge/ /volume/LDUOnlineJudge/

RUN set -ex &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.aliyun.com\/ubuntu/g' /etc/apt/sources.list && \
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone

RUN bash /volume/LDUOnlineJudge/install/ubuntu/install.sh

RUN chmod +x /volume/LDUOnlineJudge/install/docker/startup.sh

ENTRYPOINT ["/volume/LDUOnlineJudge/install/docker/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
