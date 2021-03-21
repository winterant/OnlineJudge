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
         > /etc/apt/sources.list &&\
    apt-get update && apt-get -y upgrade &&\
    apt-get -y install software-properties-common &&\
    echo -e "\n" | apt-add-repository ppa:ondrej/php &&\
    apt-get update &&\
    apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml &&\

    cd /home/LDUOnlineJudge &&\
    cp -rf .env.example .env &&\
    mkdir -p /home/LDUOnlineJudge/storage/app/public &&\
    chmod -R 777 storage bootstrap/cache &&\
    apt-get -y install composer zip unzip &&\
    composer install --ignore-platform-reqs &&\
    php artisan storage:link &&\
    php artisan key:generate &&\
    php artisan optimize

RUN set -ex &&\
    apt -y install nginx &&\
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp -f /home/LDUOnlineJudge/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf

RUN set -ex &&\
    apt-get -y install mysql-server &&\
    usermod -d /var/lib/mysql/ mysql &&\
#    chown -R mysql:mysql /var/lib/mysql /var/run/mysqld &&\
    service mysql restart &&\
    USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'` &&\
    PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'` &&\
    mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE If Not Exists lduoj;" &&\
    mysql -u${USER} -p${PASSWORD} -e"CREATE USER If Not Exists 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';" &&\
    mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;" &&\
    mysql -u${USER} -p${PASSWORD} -Dlduoj < /home/LDUOnlineJudge/install/mysql/lduoj.sql

RUN set -ex &&\
    apt -y install sudo &&\
    echo "root ALL=(ALL) ALL" >> /etc/sudoers &&\
    echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers &&\

    apt -y install g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex &&\
    cp -p /home/LDUOnlineJudge/judge/sim/sim.1 /usr/share/man/man1/ &&\
    cd /home/LDUOnlineJudge/judge/sim/ && make install &&\

    cp /home/LDUOnlineJudge/install/docker/startup.sh / &&\
    chmod +x /startup.sh

RUN set -ex &&\
    mv /home/LDUOnlineJudge /volume/ &&\
    ln -s /volume/LDUOnlineJudge /home/LDUOnlineJudge &&\
    mv /var/lib/mysql /volume/ &&\
    ln -s /volume/mysql /var/lib/mysql &&\
    chown -R mysql:mysql /volume/mysql

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
