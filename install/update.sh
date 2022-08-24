#!/bin/bash

set -e

# Read arg
if [ ! -n "$1" ]; then
    github_src="https://github.com/winterant/LDUOnlineJudge.git"
    gitee_src="https://gitee.com/wrant/LDUOnlineJudge.git"
    echo "---------------------------------------------------------"
    echo "Please choose a git source to download:"
    echo "[1] ${github_src}"
    echo "[2] ${gitee_src} (Default)"
    echo "[Input a code source url directly]"
    read -p "Input your choice or custom source url:" source
    if [[ "${source}" == "1" ]];then
        source=$github_src
    elif [[ "${source}" == "2" ]];then
        source=$gitee_src
    fi
fi

echo "Git source: ${source}"

# Download new code
APP_HOME=$(dirname $(dirname $(readlink -f "$0")))    # Path of old project (current)
upgrade=~/"oj_up"  # Path of new code
echo "APP HOME: ${APP_HOME}"
echo "Latest project: ${upgrade}"
rm -rf ${upgrade}
git clone ${source} ${upgrade}

# Checking path error
cd "${upgrade}" || { echo "No such folder ${upgrade}"; exit -1; }  # 检查新项目是否存在
if [[ "${upgrade}" == "${APP_HOME}" ]]; then
    echo "[Failure] path of old project and new project are the same one."
    exit -1
fi

# Updating files.
cp -rf "${upgrade}"/. "${APP_HOME}"/

# Updating laravel packages.
composer install --ignore-platform-reqs
php artisan optimize

# Updating database table structure.
bash install/mysql/update_mysql.sh

# Remove codes just downloaded.
rm -rf "${upgrade}" &

echo "You have successfully updated LDU Online Judge! Enjoy it!"
