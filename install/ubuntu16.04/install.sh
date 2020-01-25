#!/bin/sh

web_home=/home    #项目存放位置
apt -y update && apt -y upgrade

# 下载项目源码
apt -y install git
cd ${web_home} && git clone https://github.com/iamwinter/LDUOnlineJudge.git
cd ${web_home}/LDUOnlineJudge && cp .env.example .env && chmod -R 777 storage/ bootstrap/cache
apt -y remove git

# php
apt -y install software-properties-common python-software-properties
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get -y update
apt -y install php7.2 php7.2-fpm php7.2-mysql

# composer
apt -y install composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs

# laravel artisan; 必须在composer之后
php artisan key:generate
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache

# nignx
apt -y install nginx
rm -rf /etc/nginx/sites-available/default
cp -f ${web_home}/LDUOnlineJudge/install/nginx/lduoj.conf  /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt -y install mysql-server libmysqlclient-dev
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -h localhost -u${USER} -p${PASSWORD} -e"ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'rootroot';"
mysql -h localhost -u${USER} -p${PASSWORD} -e"CREATE DATABASE lduoj;"
mysql -h localhost -u${USER} -p${PASSWORD} -e"CREATE USER 'lduoj'@'localhost' IDENTIFIED BY '123456789';"
mysql -h localhost -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"
mysql -h localhost -u${USER} -p${PASSWORD} -Dlduoj < ${web_home}/LDUOnlineJudge/install/mysql/lduoj.sql


# C/C++
apt -y install g++


echo "\nYou have successfully installed LDU Online Judge!"
echo "Enjoy it!"
echo "Installation location: ${web_home}/LDUOnlineJudge"

# delete self
rm -rf ./$0
