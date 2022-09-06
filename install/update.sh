#!/bin/bash

set -ex

APP_HOME=$(dirname $(dirname $(readlink -f "$0")))    # Path of old project (current)
upgrade="$1"  # Path of new code
echo "APP HOME: ${APP_HOME}"
echo "Latest code: ${upgrade}"

# Checking path error
cd "${upgrade}" || { echo "No such folder ${upgrade}"; exit -1; }  # 检查新项目是否存在
if [[ "${upgrade}" == "${APP_HOME}" ]]; then
    echo "[Failure] path of old project and new project are the same one."
    exit -1
fi

# Updating files, package, database and configs.
cp -rf "${upgrade}"/. "${APP_HOME}"/
composer install --ignore-platform-reqs
yes|php artisan migrate
php artisan optimize

echo "You have successfully updated Online Judge. Enjoy it!"
