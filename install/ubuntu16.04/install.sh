#!/bin/sh

#更换源
cp /etc/apt/sources.list /etc/apt/sources.list.bak
echo "deb http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse" > /etc/apt/sources.list

apt update -y && apt upgrade -y
web_home=/home    #项目存放位置

#下载项目源码
apt install -y git
cd ${web_home} && git clone https://github.com/iamwinter/LDUOnlineJudge.git
cd ${web_home}/LDUOnlineJudge && cp .env.example .env && chmod -R 777 storage/ bootstrap/
apt remove -y git

#php解释器
apt install -y software-properties-common python-software-properties
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get update
apt install -y php7.2 php7.2-fpm

#nignx代理
apt install -y nginx
cp  ${web_home}/LDUOnlineJudge/install/nginx/lduoj.conf  /etc/nginx/conf.d/lduoj.conf
service nginx restart

#composer
apt install -y composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs

#mysql
apt install -y mysql-server mysql-client libmysqlclient-dev

#C/C++
apt install -y g++
