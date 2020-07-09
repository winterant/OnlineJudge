#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/lduoj_upgrade ];then
  echo "No such project: ${web_home}/lduoj_upgrade"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${web_home}/lduoj_upgrade"
  exit 1;
fi;

# transfer files
rm -rf ${web_home}/LDUOnlineJudge/app
rm -rf ${web_home}/LDUOnlineJudge/config
rm -rf ${web_home}/LDUOnlineJudge/install
rm -rf ${web_home}/LDUOnlineJudge/judge
rm -rf ${web_home}/LDUOnlineJudge/public
rm -rf ${web_home}/LDUOnlineJudge/resources
rm -rf ${web_home}/LDUOnlineJudge/routes
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
rm -rf ${web_home}/lduoj_upgrade

cd ${web_home}/LDUOnlineJudge || exit 2;

# update packages
composer install --ignore-platform-reqs

# laravel artisan
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
bash ${web_home}/LDUOnlineJudge/install/mysql/update_mysql.sh

#start to judge
bash ${web_home}/LDUOnlineJudge/judge/startup.sh

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"

## delete self
#cd `dirname $0` && rm -rf ./update.sh
