#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/lduoj_upgrade ];then
  echo "No such project: ${web_home}/lduoj_upgrade"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${web_home}/lduoj_upgrade"
  exit 1;
fi;

# transfer files
mv -f ${web_home}/lduoj_upgrade/app        ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/config     ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/install    ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/judge      ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/public     ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/resources  ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/routes     ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/.env.example     ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/composer.json    ${web_home}/LDUOnlineJudge/
mv -f ${web_home}/lduoj_upgrade/composer.lock    ${web_home}/LDUOnlineJudge/
m -rf ${web_home}/lduoj_upgrade

cd ${web_home}/LDUOnlineJudge || exit 2;

# update packages
composer install --ignore-platform-reqs

# laravel artisan
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
bash ${web_home}/LDUOnlineJudge/install/ubuntu16.04/update_mysql.sh

#start to judge
bash ${web_home}/LDUOnlineJudge/judge/startup.sh

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"

## delete self
#cd `dirname $0` && rm -rf ./update.sh
