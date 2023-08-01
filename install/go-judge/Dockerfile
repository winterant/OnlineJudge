FROM criyle/executorserver:v1.6.7

ADD ./testlib-sparkoj.h /usr/local/include/testlib.h

RUN set -ex &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.aliyun.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt update && apt upgrade -y &&\
    apt install -y gcc g++ openjdk-8-jre openjdk-8-jdk golang
