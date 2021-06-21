FROM ubuntu:16.04

COPY LDUOnlineJudge/ /home/LDUOnlineJudge/

RUN set -ex &&\
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\

    cp /etc/apt/sources.list /etc/apt/sources.list.bak &&\
    echo "deb http://mirrors.163.com/ubuntu/ bionic main restricted universe multiverse\
        deb http://mirrors.163.com/ubuntu/ bionic-security main restricted universe multiverse\
        deb http://mirrors.163.com/ubuntu/ bionic-updates main restricted universe multiverse\
        deb http://mirrors.163.com/ubuntu/ bionic-proposed main restricted universe multiverse\
        deb http://mirrors.163.com/ubuntu/ bionic-backports main restricted universe multiverse\
        deb-src http://mirrors.163.com/ubuntu/ bionic main restricted universe multiverse\
        deb-src http://mirrors.163.com/ubuntu/ bionic-security main restricted universe multiverse\
        deb-src http://mirrors.163.com/ubuntu/ bionic-updates main restricted universe multiverse\
        deb-src http://mirrors.163.com/ubuntu/ bionic-proposed main restricted universe multiverse\
        deb-src http://mirrors.163.com/ubuntu/ bionic-backports main restricted universe multiverse"\
         > /etc/apt/sources.list

RUN set -ex &&\
    apt-get update && apt-get -y upgrade &&\
    apt -y install sudo &&\
    echo "root ALL=(ALL) ALL" >> /etc/sudoers &&\
    echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers &&\

RUN bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
