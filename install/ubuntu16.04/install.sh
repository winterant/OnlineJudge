#!/bin/sh

web_home=/home    #项目存放位置
apt update -y && apt upgrade -y

# 下载项目源码
apt install -y git
cd ${web_home} && git clone https://github.com/iamwinter/LDUOnlineJudge.git
cd ${web_home}/LDUOnlineJudge && cp .env.example .env && chmod -R 777 storage/ bootstrap/cache
apt remove -y git

# php
apt install -y software-properties-common python-software-properties
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get update
apt install -y php7.2 php7.2-fpm

# composer
apt install -y composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs

# laravel artisan; 必须在composer之后
php artisan key:generate
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache

# nignx
apt install -y nginx
rm -rf /etc/nginx/sites-available/default
cp -f ${web_home}/LDUOnlineJudge/install/nginx/lduoj.conf  /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt install -y mysql-server libmysqlclient-dev
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
service mysql restart
echo "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';"|mysql -u${USER} -p${PASSWORD}
echo "CREATE DATABASE lduoj;"|mysql -u${USER} -p${PASSWORD}
echo "CREATE USER 'lduoj'@'localhost' IDENTIFIED BY '123456789';"|mysql -u${USER} -p${PASSWORD}
echo "GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"|mysql -u${USER} -p${PASSWORD}
mysql -u${USER} -p${PASSWORD} -Dlduoj < ${web_home}/LDUOnlineJudge/install/mysql/lduoj.sql


# C/C++
apt install -y g++


echo "You have successfully installed LDU Online Judge!"
echo "Enjoy it!"
echo "Installation location: ${web_home}/LDUOnlineJudge"

# delete self
rm -rf ./$0
