#!/bin/sh

set -x
web_home=/home    #项目存放位置

if [ ! -d ${web_home}/lduoj_upgrade ];then
  echo "No such project: ${web_home}/lduoj_upgrade"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${web_home}/lduoj_upgrade"
  exit 1;
fi;

# transfer files
rm -rf ${web_home}/lduoj_upgrade/storage
mv -f ${web_home}/LDUOnlineJudge/storage ${web_home}/lduoj_upgrade/
mv -f ${web_home}/LDUOnlineJudge/vendor  ${web_home}/lduoj_upgrade/
mv -f ${web_home}/LDUOnlineJudge/.env    ${web_home}/lduoj_upgrade/
mv -f ${web_home}/LDUOnlineJudge/public/favicon.ico  ${web_home}/lduoj_upgrade/public/
rm -rf ${web_home}/LDUOnlineJudge
mv ${web_home}/lduoj_upgrade  ${web_home}/LDUOnlineJudge

cd ${web_home}/LDUOnlineJudge || exit 2;
chmod -R 777 storage bootstrap/cache

# update packages
composer install --ignore-platform-reqs

# laravel artisan
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE IF NOT EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -Dlduoj_upgrade < ${web_home}/LDUOnlineJudge/install/mysql/lduoj.sql
php ${web_home}/LDUOnlineJudge/install/mysql/structure_sync.php | mysql -u${USER} -p${PASSWORD} -Dlduoj
mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"

#start to judge
bash ${web_home}/LDUOnlineJudge/judge/startup.sh

# delete self
cd `dirname $0` && rm -rf ./update.sh
