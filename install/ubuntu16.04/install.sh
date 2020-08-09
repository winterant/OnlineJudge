#!/bin/sh

set -ex
if [ ! -d /home/LDUOnlineJudge ];then
  echo No such project: /home/LDUOnlineJudge
  exit 1;
fi;
cd /home/LDUOnlineJudge

# 文件权限
cp -rf .env.example .env
chmod -R 777 storage bootstrap/cache

# php
apt -y update && apt -y upgrade
apt -y install software-properties-common
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt -y update
apt -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml
service php7.2-fpm start

# composer
apt -y install composer zip unzip
composer install --ignore-platform-reqs

# laravel artisan; 依赖composer
php artisan storage:link
php artisan key:generate
php artisan optimize

# nignx
apt -y install nginx
rm -rf /etc/nginx/sites-enabled/default
cp -f ./install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt -y install mysql-server
service mysql restart
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE lduoj;"
mysql -u${USER} -p${PASSWORD} -e"CREATE USER 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"
mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"
mysql -u${USER} -p${PASSWORD} -Dlduoj < ./install/mysql/lduoj.sql

# Allow php user www-data to use 'sudo' to get privilege of root
# If you don't grant the right to user www-data, then you will not be able to start or stop the judge in administration.
echo 'www-data ALL = NOPASSWD: ALL' >> /etc/sudoers

# sim config
apt -y install make flex
cp -p ./judge/sim/sim.1 /usr/share/man/man1/
cd ./judge/sim/ && make install && cd ../../

#install judge environment & start to judge
apt update && apt -y upgrade
apt -y install libmysqlclient-dev g++
apt -y install openjdk-8-jdk
apt -y install python3.6
bash ./judge/startup.sh

echo -e "You have successfully installed LDU Online Judge!"
