#!/bin/sh

set -x
APP_HOME=$1    # 原项目位置
upgrade=$(dirname $(dirname $(dirname $(readlink -f "$0"))))  # 新版本位置

if [[ "${APP_HOME}" == "" ]]; then
    read -p "Please input old project location (such as /home/LDUOnlineJudge):" APP_HOME
fi

# update files
cp -rf "${APP_HOME}"/install/nginx/lduoj.conf "${upgrade}"/install/nginx/
cp -rf "${APP_HOME}"/public/favicon.ico       "${upgrade}"/public/
cp -rf "${upgrade}"/*                         "${APP_HOME}"/

# update laravel packages
cd "${APP_HOME}" || exit 2
chmod -R 755 storage bootstrap/cache
chown www-data:www-data -R storage bootstrap/cache
composer install --ignore-platform-reqs
php artisan storage:link
php artisan key:generate
php artisan optimize

# update mysql table schema
bash "${APP_HOME}"/install/mysql/update_mysql.sh

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
    bash "${APP_HOME}"/judge/startup.sh
fi

# delete upgrade
rm -rf "${upgrade}" &

echo "You have successfully updated LDU Online Judge! Enjoy it!\n"
