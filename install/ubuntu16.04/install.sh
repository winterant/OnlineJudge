#!/bin/sh

set -x
root=/home/LDUOnlineJudge
cd ${root} || { echo No such project: ${root};exit 1; }

# php environment
apt-get update && apt-get -y upgrade
apt-get -y install software-properties-common
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get update
apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml
service php7.2-fpm start

# composer
apt -y install composer zip unzip
composer install --ignore-platform-reqs

# laravel initialization
chmod -R 777 storage bootstrap/cache
cp -rf .env.example .env
mkdir -p storage/app/public
php artisan storage:link
php artisan key:generate
php artisan optimize

# nignx
apt -y install nginx
rm -rf /etc/nginx/sites-enabled/default
cp -f ${root}/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt -y install mysql-server
service mysql restart
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE If Not Exists lduoj;"
mysql -u${USER} -p${PASSWORD} -e"CREATE USER If Not Exists 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"
mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"
mysql -u${USER} -p${PASSWORD} -Dlduoj < ./install/mysql/lduoj.sql

# Allow php user www-data to use 'sudo' to get privilege of root
# If you don't grant the right to user www-data, then you will not be able to start or stop the judge in administration.
echo 'www-data ALL = NOPASSWD: ALL' >> /etc/sudoers

# install judge environment & sim config & start to judge
apt -y install g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex
cp -p ${root}/judge/sim/sim.1 /usr/share/man/man1/
cd ${root}/judge/sim/ && make install
bash ${root}/judge/startup.sh

echo "You have successfully installed LDU Online Judge!"
