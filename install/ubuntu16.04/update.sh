#!/bin/sh

set -x
root=/home/LDUOnlineJudge    #项目
upgrade=/home/lduoj_upgrade  #新版本

if [ ! -d ${upgrade} ];then
  echo "No such project: ${upgrade}"
  echo "Please first: git clone https://github.com/iamwinter/LDUOnlineJudge.git ${upgrade}"
  exit 1;
fi;

# transfer files
rm -rf ${root}/app
rm -rf ${root}/config
rm -rf ${root}/install
rm -rf ${root}/judge
rm -rf ${root}/public
rm -rf ${root}/resources
rm -rf ${root}/routes
mv -f ${upgrade}/app        ${root}/
mv -f ${upgrade}/config     ${root}/
mv -f ${upgrade}/install    ${root}/
mv -f ${upgrade}/judge      ${root}/
mv -f ${upgrade}/public     ${root}/
mv -f ${upgrade}/resources  ${root}/
mv -f ${upgrade}/routes     ${root}/
mv -f ${upgrade}/composer.json ${root}/
mv -f ${upgrade}/composer.lock ${root}/
mv -f ${upgrade}/.env.example  ${root}/
mv -f ${upgrade}/readme.md     ${root}/

cd ${root} || exit 2

# update laravel packages
composer install --ignore-platform-reqs
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
bash ${root}/install/mysql/update_mysql.sh

# sim config
cd ${root}/judge/sim/ || exit 3
make install

#start to judge
bash ${root}/judge/startup.sh

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"

# delete upgrade
rm -rf ${upgrade} &
