FROM ubuntu:20.04

RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.163.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt-get update && apt-get -y upgrade &&\
    apt-get -y install software-properties-common &&\
    apt-get -y install language-pack-en-base &&\
    echo -e "\n" | add-apt-repository ppa:deadsnakes/ppa &&\
    echo -e "\n" | apt-add-repository ppa:ondrej/php &&\
    apt-get update && apt-get -y upgrade &&\
    apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring &&\
    apt-get -y install composer zip unzip &&\
    apt-get -y install nginx &&\
    apt-get -y install mysql-server &&\
    apt-get -y install g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex &&\
    apt-get -y install sudo vim

COPY . /home/LDUOnlineJudge/

WORKDIR /home/LDUOnlineJudge

RUN export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8 &&\
    composer install --ignore-platform-reqs &&\
    chown www-data:www-data -R storage bootstrap/cache &&\
    cp -rf .env.example .env &&\
    cp -rf judge/config.sh.sample judge/config.sh &&\
    cp -rf public/favicon.ico.sample public/favicon.ico &&\
    php artisan storage:link &&\
    php artisan key:generate &&\
    php artisan optimize &&\
    # sim
    cp -rf judge/sim/sim.1 /usr/share/man/man1/ &&\
    cd judge/sim/ && make install && cd ../../ &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp -rf install/nginx/lduoj.conf /etc/nginx/conf.d/ &&\
    # php7.2-fpm
    mv /etc/php/7.2/fpm/pool.d/www.conf /etc/php/7.2/fpm/pool.d/www.conf.bak &&\
    cp -rf install/php-fpm/www.conf /etc/php/7.2/fpm/pool.d/ &&\
    # docker startup
    cp -rf install/docker/startup.sh /startup.sh &&\
    chmod +x /startup.sh &&\
    # sudoers
    echo 'www-data ALL=(ALL) NOPASSWD: /bin/bash,/usr/bin/git,/usr/bin/g++' >> /etc/sudoers

RUN USER=$(cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}') &&\
    PASSWORD=$(cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}') &&\
    usermod -d /var/lib/mysql/ mysql &&\
    service mysql restart &&\
    rand_passwd=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-16}) &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE DATABASE If Not Exists lduoj;" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE USER If Not Exists 'oj_user'@'localhost' IDENTIFIED WITH mysql_native_password BY '${rand_passwd}';" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"GRANT all privileges ON lduoj.* TO 'oj_user'@'localhost';flush privileges;" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -Dlduoj < install/mysql/lduoj.sql &&\
    sed -i "s/^DB_DATABASE.*$/DB_DATABASE=lduoj/"       .env  judge/config.sh &&\
    sed -i "s/^DB_USERNAME.*$/DB_USERNAME=oj_user/"        .env  judge/config.sh &&\
    sed -i "s/^DB_PASSWORD.*$/DB_PASSWORD=${rand_passwd}/" .env  judge/config.sh

WORKDIR /volume

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
