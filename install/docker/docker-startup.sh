#!/bin/bash

set -e
sleep 5 # Waiting for mysql being started.

# If host machine has not files, give it files.
if [ ! -d "/app/app" ];then
    echo "Copying files to /app"
    yes|cp -rf /app_tmp/. /app/
fi

# work dir.
cd /app

# Receive arguments and modify environment
function mod_env(){  # key,value
    sed -i "s/^.\?$1.*$/$1=$2/" .env
}
mod_env "DB_HOST"           ${DB_HOST:-localhost}
mod_env "DB_PORT"           ${DB_PORT:-3306}
mod_env "DB_DATABASE"       ${DB_DATABASE:-lduoj}
mod_env "DB_USERNAME"       ${DB_USERNAME:-oj_user}
mod_env "DB_PASSWORD"       ${DB_PASSWORD:-OurFutrue2045}
mod_env "JUDGE0_SERVER"     ${JUDGE0_SERVER:-judge0-server:2358}
mod_env "HREF_FORCE_HTTPS"  ${HREF_FORCE_HTTPS:-false}

# change owner
chown www-data:www-data -R storage bootstrap/cache

# start php-fpm
service php7.2-fpm start
php artisan storage:link
php artisan optimize
yes|php artisan migrate
yes|php artisan key:generate
php artisan optimize

# start nginx
service nginx start

sleep infinity
