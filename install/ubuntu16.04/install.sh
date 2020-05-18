#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/LDUOnlineJudge ];then
  echo No such project: ${web_home}/LDUOnlineJudge
  exit 1;
fi;
cd ${web_home}/LDUOnlineJudge

# 文件权限
cp -rf .env.example .env
chmod -R 777 storage bootstrap/cache

# php
apt -y update && apt -y upgrade
apt -y install software-properties-common python-software-properties
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt -y update
apt -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml
service php7.2-fpm start

# composer
apt -y install composer zip unzip
composer install --ignore-platform-reqs

# laravel artisan; 必须在composer之后
php artisan storage:link
php artisan key:generate
php artisan optimize

# nignx
apt -y install nginx
rm -rf /etc/nginx/sites-enabled/default
cp -f ${web_home}/LDUOnlineJudge/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
service nginx restart

# mysql
apt -y install mysql-server
service mysql restart
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE lduoj;"
mysql -u${USER} -p${PASSWORD} -e"CREATE USER 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"
mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"
mysql -u${USER} -p${PASSWORD} -Dlduoj < ${web_home}/LDUOnlineJudge/install/mysql/lduoj.sql


# allow php user www-data to use 'sudo' to get privilege of root, for sudo polling
echo 'www-data ALL = NOPASSWD: ALL' >> /etc/sudoers

echo -e "You have successfully installed LDU Online Judge!"

#install judge environment & start to judge
bash ${web_home}/LDUOnlineJudge/judge/install.sh
bash ${web_home}/LDUOnlineJudge/judge/startup.sh
