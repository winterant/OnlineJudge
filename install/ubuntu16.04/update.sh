#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/lduoj_temp ];then
  echo "No such project: ${web_home}/lduoj_temp"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${web_home}/lduoj_temp"
  exit 1;
fi;

# transfer files
rm -rf ${web_home}/lduoj_temp/storage
mv -f ${web_home}/LDUOnlineJudge/storage ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/vendor  ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/.env    ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/public/favicon.ico  ${web_home}/lduoj_temp/public/
rm -rf ${web_home}/LDUOnlineJudge
mv ${web_home}/lduoj_temp  ${web_home}/LDUOnlineJudge

cd ${web_home}/LDUOnlineJudge || exit 2;
chmod -R 777 storage bootstrap/cache
chown -R www-data:www-data ${web_home}/LDUOnlineJudge/config/oj/main.php

# update packages
composer install --ignore-platform-reqs

# laravel artisan
php artisan storage:link
php artisan key:generate
php artisan optimize

echo "You have successfully updated LDU Online Judge! Enjoy it!"

#start to judge
bash ${web_home}/LDUOnlineJudge/judge/startup.sh

# delete shell file
cd `dirname $0` && rm -rf ./update.sh
