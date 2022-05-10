#!/bin/sh

APP_HOME=/volume/LDUOnlineJudge    # 原项目位置
upgrade=$(dirname $(dirname $(dirname $(readlink -f "$0"))))  # 新版本位置
cd "${APP_HOME}" || { echo "No such folder ${APP_HOME}"; exit 1; }

echo "APP HOME: ${upgrade}"
echo "New project: ${APP_HOME}"
if [[ ${upgrade} == ${APP_HOME} ]]; then
    echo "[Failure] Please execute update.sh in new project instead of current project."
    exit -1
fi

set -x

# 更新文件
cp -rf "${upgrade}"/. "${APP_HOME}"/


# 更新laravel依赖包
composer install --ignore-platform-reqs
php artisan optimize

# 更新mysql表结构信息
bash install/mysql/update_mysql.sh

if [ -f /.dockerenv ]; then
    # nginx config
    cp -rf install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf
    service nginx restart
    # docker startup
    cp -rf install/docker/startup.sh /startup.sh
    chmod +x /startup.sh
    nohup bash /startup.sh > /dev/null 2>&1 &
    sleep 1  # nohup后台执行，sleep保证后面的命令最后执行
fi

# 删除升级包
rm -rf "${upgrade}" &

echo "You have successfully updated LDU Online Judge! Enjoy it!"
