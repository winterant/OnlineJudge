FROM ubuntu:16.04

RUN set -ex\
    && cp /etc/apt/sources.list /etc/apt/sources.list.bak\
    && echo "deb http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse\
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse\
        deb http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse\
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse\
        deb http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse\
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse\
        deb http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse\
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse\
        deb http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse\
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse" > /etc/apt/sources.list\
    && apt-get update && apt-get -y upgrade\

    && apt-get -y install dnsutils network-manager\
    && GIT_IP1=`nslookup github.global.ssl.fastly.net |grep Address:|tail -1|awk '{print $2}'`\
    && GIT_IP2=`nslookup github.com |grep Address:|tail -1|awk '{print $2}'`\
    && echo "$GIT_IP1 github.global.ssl.fastly.net" >> /etc/hosts\
    && echo "$GIT_IP2 github.com" >> /etc/hosts\
    && service network-manager restart\

    && apt-get -y purge git\
    && apt-get -y install git\
    && git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge\
    && apt-get -y autoremove git\

    && cd /home/LDUOnlineJudge\
    && cp -rf .env.example .env\
    && chmod -R 777 storage bootstrap/cache\

    && apt-get update && apt-get -y upgrade\
    && apt-get -y install software-properties-common\
    && echo -e "\n" | apt-add-repository ppa:ondrej/php\
    && apt-get update\
    && apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml\

    && apt-get -y install composer zip unzip\
    && composer install --ignore-platform-reqs\
    && php artisan storage:link\
    && php artisan key:generate\
    && php artisan optimize\

    && apt -y install nginx\
    && rm -rf /etc/nginx/sites-enabled/default\
    && cp -f /home/LDUOnlineJudge/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf\

    && apt-get -y install mysql-server\
    && usermod -d /var/lib/mysql/ mysql\
#    && rm -rf /var/lib/mysql/*\
#    && mysqld --initialize --explicit_defaults_for_timestamp=true\
    && service mysql restart\
#    && chown -R mysql:mysql /var/lib/mysql /var/run/mysqld\
    && USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`\
    && PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`\
    && mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE lduoj;"\
    && mysql -u${USER} -p${PASSWORD} -e"CREATE USER 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"\
    && mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"\
    && mysql -u${USER} -p${PASSWORD} -Dlduoj < /home/LDUOnlineJudge/install/mysql/lduoj.sql\

    && apt -y install sudo\
    && echo "root ALL=(ALL) ALL" >> /etc/sudoers\
    && echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers\

    && apt update\
    && apt -y install libmysqlclient-dev g++ openjdk-8-jdk python3.6\

    && echo "#!/bin/bash\n\
        mv /home/LDUOnlinejudge /volume/\n\
        ln -s /volume/LDUOnlinejudge /home/LDUOnlinejudge\n\
        mv /var/lib/mysql /volume/\n\
        ln -s /volume/mysql /var/lib/mysql\n\
        chmod -R /volme\n\
        service nginx start\n\
        service php7.2-fpm start\n\
        service mysql start\n\
        bash /home/LDUOnlineJudge/judge/startup.sh\n\
        while true; do echo do not quit; sleep 1; done\n" > /startup.sh\
    && chmod +x /startup.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
