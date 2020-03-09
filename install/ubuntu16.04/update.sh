#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/lduoj_temp ];then
  echo "No such project: ${web_home}/lduoj_temp"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${web_home}/lduoj_temp"
  exit 1;
fi;

rm -rf ${web_home}/lduoj_temp/storage

mv -f ${web_home}/LDUOnlineJudge/storage ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/vendor  ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/.env    ${web_home}/lduoj_temp/
mv -f ${web_home}/LDUOnlineJudge/public/favicon.ico  ${web_home}/lduoj_temp/public/
rm -rf ${web_home}/LDUOnlineJudge
mv -f ${web_home}/lduoj_temp  ${web_home}/LDUOnlineJudge

# shellcheck disable=SC2164
cd ${web_home}/LDUOnlineJudge
chmod -R 777 storage bootstrap/cache

# composer
composer install --ignore-platform-reqs

# laravel artisan
php artisan storage:link
php artisan key:generate
php artisan config:cache
php artisan route:cache

echo "You have successfully updated LDU Online Judge! Enjoy it!"
