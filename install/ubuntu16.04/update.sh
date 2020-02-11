#!/bin/sh

set -x

web_home=/home    #项目存放位置
backup='lduoj_'$(date "+%Y%m%d_%H%M%S")

# 项目备份
mkdir -p ${web_home}/lduoj_backup/${backup}

mv -f ${web_home}/LDUOnlineJudge ${web_home}/lduoj_backup/${backup}
# 数据库备份
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysqldump -u${USER} -p${PASSWORD} -B lduoj > ${web_home}/lduoj_backup/${backup}/lduoj.sql
# nginx备份
cp -r -f -p /etc/nginx/conf.d/lduoj.conf ${web_home}/lduoj_backup/${backup}/lduoj.nginx.conf


# 下载项目源码
apt update
apt install -y git
cd ${web_home} && git clone https://github.com/iamwinter/LDUOnlineJudge.git
apt -y remove git
rm -rf ${web_home}/LDUOnlineJudge/storage
mv -f ${web_home}/lduoj_backup/${backup}/LDUOnlineJudge/storage ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_backup/${backup}/LDUOnlineJudge/vendor  ${web_home}/LDUOnlineJudge/
cp -r -p -f ${web_home}/lduoj_backup/${backup}/LDUOnlineJudge/.env ${web_home}/LDUOnlineJudge/.env
cp -r -p -f ${web_home}/lduoj_backup/${backup}/LDUOnlineJudge/public/favicon.ico ${web_home}/LDUOnlineJudge/public/favicon.ico
chmod -R 777 ${web_home}/LDUOnlineJudge/bootstrap/cache


# composer
apt install -y composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs
# laravel artisan
php artisan key:generate
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

echo -e "\nYou have successfully updated LDU Online Judge! Enjoy it!"
echo -e "Installation location: ${web_home}/LDUOnlineJudge"
