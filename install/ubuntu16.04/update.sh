#!/bin/sh

set -x
APP_HOME=$1    # 原项目位置
upgrade=$(dirname $(dirname $(dirname $(readlink -f "$0"))))  # 新版本位置

if [[ "${APP_HOME}" == "" ]]; then
    read -p "Please input old project location (such as /home/LDUOnlineJudge):" APP_HOME
fi

# transfer files
rm -rf ${APP_HOME}/.git
rm -rf ${APP_HOME}/app
rm -rf ${APP_HOME}/config
rm -rf ${APP_HOME}/install/{docker,mysql,ubuntu16.04}
rm -rf ${APP_HOME}/judge/{cpp,java.policy,startup.sh,stop.sh}
rm -rf ${APP_HOME}/public/{css,images,js,static}
rm -rf ${APP_HOME}/resources
rm -rf ${APP_HOME}/routes

mv -f ${upgrade}/.git       ${APP_HOME}/
mv -f ${upgrade}/app        ${APP_HOME}/
mv -f ${upgrade}/config     ${APP_HOME}/
mv -f ${upgrade}/install/{docker,mysql,ubuntu16.04}    ${APP_HOME}/install/
mv -f ${upgrade}/judge/{cpp,java.policy,startup.sh,stop.sh}      ${APP_HOME}/judge/
mv -f ${upgrade}/public/{css,images,js,static}     ${APP_HOME}/public/
mv -f ${upgrade}/resources  ${APP_HOME}/
mv -f ${upgrade}/routes     ${APP_HOME}/
mv -f ${upgrade}/composer.json ${APP_HOME}/
mv -f ${upgrade}/composer.lock ${APP_HOME}/
mv -f ${upgrade}/.env.example  ${APP_HOME}/
mv -f ${upgrade}/readme.md     ${APP_HOME}/


# update laravel packages
cd ${APP_HOME} || exit 2
chmod -R 755 storage bootstrap/cache
chown www-data:www-data -R storage bootstrap/cache
composer install --ignore-platform-reqs
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
bash ${APP_HOME}/install/mysql/update_mysql.sh

# docker startup
if [ -f /.dockerenv ]; then
    rm -rf /startup.sh
    chmod +x "${APP_HOME}"/install/docker/startup.sh
    ln -s "${APP_HOME}"/install/docker/startup.sh /startup.sh
    nohup bash /startup.sh > /startup_nohup.txt 2>&1 &
    sleep 1  # nohup后台执行，防止他执行的顺序比后面的语句晚
fi

# If not in docker, start to judge
if [ ! -f /.dockerenv ]; then
    bash ${APP_HOME}/judge/startup.sh
fi

# delete upgrade
rm -rf ${upgrade} &

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"
