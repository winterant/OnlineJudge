#!/bin/bash

set -ex
sleep 5 # Waiting for mysql being started.

# If host machine has not files, give it files.
if [ ! -d "/app/public" ];then
    echo "Copying files from /app_tmp to /app"
    yes|cp -rf /app_tmp/. /app/
fi

# Receive arguments and modify environment of laravel .env
function mod_env(){
    sed -i "s/^.\?$1.*$/$1=${2//\//\\\/}/" .env
}
mod_env "APP_DEBUG"         ${APP_DEBUG:-false}
mod_env "DB_HOST"           ${DB_HOST:-host.docker.internal}
mod_env "DB_PORT"           ${DB_PORT:-3306}
mod_env "DB_DATABASE"       ${DB_DATABASE:-lduoj}
mod_env "DB_USERNAME"       ${DB_USERNAME:-oj_user}
mod_env "DB_PASSWORD"       ${DB_PASSWORD:-OurFutrue2045}
mod_env "JUDGE0_SERVER"     ${JUDGE0_SERVER:-host.docker.internal:2358}
mod_env "HREF_FORCE_HTTPS"  ${HREF_FORCE_HTTPS:-false}
mod_env "QUEUE_CONNECTION"  ${QUEUE_CONNECTION:-database}

# start nginx server
service nginx start

# Change storage folders owner.
chown www-data:www-data -R storage bootstrap/cache

# Start php-fpm server and initialize laravel app.
service php7.2-fpm start
php artisan storage:link
php artisan optimize
yes|php artisan migrate
yes|php artisan key:generate
php artisan optimize

# Start laravel-queue
php artisan queue:work

# Sleep forever to keep container alives.
sleep infinity
