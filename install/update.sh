#!/bin/bash

set -ex

APP_HOME="${1:-/app}"  # Path of production
upgrade=$(dirname $(dirname $(readlink -f "$0")))    # Path of new code (current)
echo "APP HOME: ${APP_HOME}"
echo "Latest code: ${upgrade}"

# Updating files, package, database and configs.
cp -rf "${upgrade}"/. "${APP_HOME}"/
composer install --ignore-platform-reqs
yes|php artisan migrate
php artisan optimize

echo "You have successfully updated Online Judge. Enjoy it!"
