#!/bin/sh

set -ex

APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))
cd "${APP_HOME}" || { echo No such project: "${APP_HOME}";exit 1; }

# 更新/添加软件源
apt-get update && apt-get -y upgrade
apt-get -y install software-properties-common
apt-get -y install language-pack-en-base
echo -e "\n" | add-apt-repository ppa:deadsnakes/ppa
echo -e "\n" | apt-add-repository ppa:ondrej/php
apt-get update
export LC_ALL=en_US.UTF-8
export LANG=en_US.UTF-8

# 安装php环境，使用composer安装依赖
apt-get -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring
apt-get -y install composer zip unzip
composer install --ignore-platform-reqs
service php7.2-fpm start

# 初始化laravel项目
cp -rf .env.example .env
mkdir -p storage/app/public
chown www-data:www-data -R storage bootstrap/cache
php artisan storage:link
php artisan key:generate
php artisan optimize

# 安装nignx并配置
apt-get -y install nginx
rm -rf /etc/nginx/sites-enabled/default
rm -rf /etc/nginx/conf.d/lduoj.conf
sed -i "s/\/home\/LDUOnlineJudge/${APP_HOME//\//\\\/}/" "${APP_HOME}"/install/nginx/lduoj.conf
ln -sf "${APP_HOME}"/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
service nginx restart

# 安装mysql并配置
apt-get -y install mysql-server
if [ -f /.dockerenv ]; then
    usermod -d /var/lib/mysql/ mysql
fi
service mysql restart
USER=$(cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}')
PASSWORD=$(cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}')
mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE DATABASE If Not Exists lduoj;"
mysql -u"${USER}" -p"${PASSWORD}" -e"CREATE USER If Not Exists 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"
mysql -u"${USER}" -p"${PASSWORD}" -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost';flush privileges;"
mysql -u"${USER}" -p"${PASSWORD}" -Dlduoj < ./install/mysql/lduoj.sql

# 安装judge环境，编译sim插件（代码查重），启动判题进程
apt-get -y install g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex
cp -p "${APP_HOME}"/judge/sim/sim.1 /usr/share/man/man1/
cd "${APP_HOME}"/judge/sim/ && make install
bash "${APP_HOME}"/judge/startup.sh

# 对于一些必要命令，为用户www-data赋予sudo权限
if [ -f /.dockerenv ]; then
    apt-get -y install sudo
fi
echo 'www-data ALL=(ALL) NOPASSWD: /bin/bash,/usr/bin/git,/usr/bin/g++' >> /etc/sudoers
# 用户名 主机名(ALL所有主机)=(用户名,以该用户运行命令,ALL表示任意用户) NOPASSWD不需要输入密码: 命令的绝对路径(逗号分隔)ALL表示所有命令

echo "You have successfully installed LDU Online Judge! Location: ${APP_HOME}"
