#!/bin/sh

set -x
APP_HOME=$1    # 原项目位置
upgrade=$(dirname $(dirname $(dirname $(readlink -f "$0"))))  # 新版本位置
cd "${APP_HOME}" || { echo "No such a folder ${APP_HOME}"; exit 1; }

# 更新文件
cp -rf "${APP_HOME}"/install/nginx/lduoj.conf "${upgrade}"/install/nginx/
cp -rf "${APP_HOME}"/public/favicon.ico       "${upgrade}"/public/
cp -rf "${upgrade}"/.                         "${APP_HOME}"/

# 更新laravel依赖包
chown www-data:www-data -R storage bootstrap/cache
composer install --ignore-platform-reqs
php artisan storage:link
php artisan optimize

# 更新mysql表结构信息
bash install/mysql/update_mysql.sh

# docker startup
if [ -f /.dockerenv ]; then
    cp -f install/docker/startup.sh /
    chmod +x /startup.sh
    nohup bash /startup.sh > /dev/null 2>&1 &
    sleep 1  # nohup后台执行，防止他执行的顺序比后面的语句晚
else
    # 手动重启判题进程
    bash judge/startup.sh
fi

# 删除升级包
rm -rf "${upgrade}" &

echo "You have successfully updated LDU Online Judge! Enjoy it!"
