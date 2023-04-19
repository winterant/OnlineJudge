FROM criyle/executorserver:v1.6.7

ENV TZ=Asia/Shanghai

RUN set -ex &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.aliyun.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt update && apt upgrade -y &&\
    apt install -y gcc g++ openjdk-8-jre openjdk-8-jdk golang

WORKDIR /opt

EXPOSE 5050/tcp 5051/tcp

ENTRYPOINT ["./executorserver"]
