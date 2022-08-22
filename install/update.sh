#!/bin/bash

# 该脚本务必在新下载的代码中执行，以覆盖原项目
# 该脚本不可以在线上项目中执行！

APP_HOME=/LDUOnlineJudge    # 原项目位置
upgrade=$(dirname $(dirname $(readlink -f "$0")))  # 新版本位置
cd "${APP_HOME}" || { echo "No such folder ${APP_HOME}"; exit 1; }  # 检查原项目是否存在

echo "APP HOME: ${APP_HOME}"
echo "Latest project: ${upgrade}"
if [[ ${upgrade} == ${APP_HOME} ]]; then
    echo "[Failure] Please execute update.sh in new project instead of online project."
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
    # docker startup
    chmod +x install/docker/startup.sh
    nohup bash install/docker/startup.sh > /dev/null 2>&1 &
    sleep 1  # nohup后台执行，sleep保证后面的命令最后执行
fi

# 删除升级包
rm -rf "${upgrade}" &

echo "You have successfully updated LDU Online Judge! Enjoy it!"
