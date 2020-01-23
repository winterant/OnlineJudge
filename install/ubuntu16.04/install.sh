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

# nignx
apt install -y nginx
rm -rf /etc/nginx/sites-available/default
cp -f ${web_home}/LDUOnlineJudge/install/nginx/lduoj.conf  /etc/nginx/conf.d/lduoj.conf
service nginx restart

# composer
apt install -y composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs

# mysql
apt install -y mysql-server mysql-client libmysqlclient-dev

# C/C++
apt install -y g++


echo "You have successfully installed LDU Online Judge!"
echo "Enjoy it!"
echo "Installation location: ${web_home}/LDUOnlineJudge"
# delete self
rm -rf ${web_home}/install.sh
