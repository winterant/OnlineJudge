FROM ubuntu:20.04

COPY . /LDUOnlineJudge/

WORKDIR /LDUOnlineJudge

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
    apt-get -y install php7.2-gd php7.2-curl &&\
    apt-get -y install composer zip unzip &&\
    apt-get -y install nginx &&\
    apt-get -y install mysql-server &&\
    apt-get -y install sudo vim &&\
    sed -i "s/^;extension=gd.*/extension=gd/"     /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^;extension=curl.*/extension=curl/" /etc/php/7.2/fpm/php.ini

RUN export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8 &&\
    composer install --ignore-platform-reqs &&\
    chown www-data:www-data -R storage bootstrap/cache &&\
    cp -rf .env.example .env &&\
    cp -rf public/favicon.ico.sample public/favicon.ico &&\
    php artisan storage:link &&\
    # I dont know why it below does not work.
    php artisan key:generate &&\
    php artisan optimize &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    ln -s /LDUOnlineJudge/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf &&\
    # php7.2-fpm
    mv /etc/php/7.2/fpm/pool.d/www.conf /etc/php/7.2/fpm/pool.d/www.conf.bak &&\
    ln -s /LDUOnlineJudge/install/php-fpm/www.conf /etc/php/7.2/fpm/pool.d/www.conf &&\
    # docker startup
    chmod +x /LDUOnlineJudge/install/docker/startup.sh &&\
    # sudoers
    echo 'www-data ALL=(ALL) NOPASSWD: /bin/bash,/usr/bin/git,/usr/bin/g++' >> /etc/sudoers

RUN USER=$(cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}') &&\
    PASSWORD=$(cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}') &&\
    usermod -d /var/lib/mysql/ mysql &&\
    service mysql restart &&\
    db_user="oj_user" &&\
    db_passwd="OurFuture2045" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE DATABASE If Not Exists lduoj;" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE USER If Not Exists '${db_user}'@'localhost' IDENTIFIED WITH mysql_native_password BY '${db_passwd}';" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -e"GRANT all privileges ON lduoj.* TO '${db_user}'@'localhost';flush privileges;" &&\
    mysql -u"${USER}" -p"${PASSWORD}" -Dlduoj < install/mysql/lduoj.sql &&\
    sed -i "s/^DB_DATABASE.*$/DB_DATABASE=lduoj/"        .env &&\
    sed -i "s/^DB_USERNAME.*$/DB_USERNAME=${db_user}/"   .env &&\
    sed -i "s/^DB_PASSWORD.*$/DB_PASSWORD=${db_passwd}/" .env

VOLUME ["/LDUOnlineJudge/storage", "/var/lib/mysql/lduoj"]

ENTRYPOINT ["/LDUOnlineJudge/install/docker/startup.sh"]

EXPOSE 80
