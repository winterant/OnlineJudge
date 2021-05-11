#!/bin/sh

set -x
root=/home/LDUOnlineJudge    #项目
upgrade=/home/lduoj_upgrade  #新版本

# transfer files
rm -rf ${root}/app
rm -rf ${root}/config
rm -rf ${root}/install
rm -rf ${root}/judge/{cpp,java.policy,startup.sh,stop.sh}
rm -rf ${root}/public
rm -rf ${root}/resources
rm -rf ${root}/routes
mv -f ${upgrade}/app        ${root}/
mv -f ${upgrade}/config     ${root}/
mv -f ${upgrade}/install    ${root}/
mv -f ${upgrade}/judge/{cpp,java.policy,startup.sh,stop.sh}      ${root}/judge/
mv -f ${upgrade}/public     ${root}/
mv -f ${upgrade}/resources  ${root}/
mv -f ${upgrade}/routes     ${root}/
mv -f ${upgrade}/composer.json ${root}/
mv -f ${upgrade}/composer.lock ${root}/
mv -f ${upgrade}/.env.example  ${root}/
mv -f ${upgrade}/readme.md     ${root}/


# update laravel packages
cd ${root} || exit 2
composer install --ignore-platform-reqs
php artisan storage:link
php artisan key:generate
php artisan optimize

# docker startup
if [ -f /startup.sh ]; then
    cp ${root}/install/docker/startup.sh /
    nohup bash ${root}/install/docker/startup.sh > /startup_nohup.txt 2>&1 &
fi

# update mysql table schema
bash ${root}/install/mysql/update_mysql.sh

#start to judge
bash ${root}/judge/startup.sh

# delete upgrade
rm -rf ${upgrade} &

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"
