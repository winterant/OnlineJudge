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
    sed -i "s/^.\?$1\s\?=.*$/$1=${2//\//\\\/}/" $3
}
mod_env "APP_DEBUG"         ${APP_DEBUG:-false}              .env
mod_env "DB_HOST"           ${DB_HOST:-host.docker.internal} .env
mod_env "DB_PORT"           ${DB_PORT:-3306}                 .env
mod_env "DB_DATABASE"       ${DB_DATABASE:-lduoj}            .env
mod_env "DB_USERNAME"       ${DB_USERNAME:-oj_user}          .env
mod_env "DB_PASSWORD"       ${DB_PASSWORD:-OurFutrue2045}    .env
mod_env "JUDGE0_SERVER"     ${JUDGE0_SERVER:-host.docker.internal:2358} .env
mod_env "HREF_FORCE_HTTPS"  ${HREF_FORCE_HTTPS:-false}       .env
mod_env "QUEUE_CONNECTION"  ${QUEUE_CONNECTION:-database}    .env

# config php, php-fpm
# open php extension
sed -i "s/^;extension=gd.*/extension=gd/"     /etc/php/7.2/fpm/php.ini
sed -i "s/^;extension=curl.*/extension=curl/" /etc/php/7.2/fpm/php.ini
sed -i "s/^;extension=zip.*/extension=zip/"   /etc/php/7.2/fpm/php.ini

# file size
mod_env "post_max_size"        ${post_max_size:-120M}       /etc/php/7.2/fpm/php.ini
mod_env "upload_max_filesize"  ${upload_max_filesize:-120M} /etc/php/7.2/fpm/php.ini

# default php-fpm `pm` for server with 32GB max memory.
mod_env "pm"                   ${pm:-dynamic}                /etc/php/7.2/fpm/pool.d/www.conf
mod_env "pm.max_children"      ${pm_max_children:-1280}      /etc/php/7.2/fpm/pool.d/www.conf
mod_env "pm.max_requests"      ${pm_max_requests:-1000}      /etc/php/7.2/fpm/pool.d/www.conf
# The following item is avaliable only if pm=dynamic.
mod_env "pm.start_servers"     ${pm_start_servers:-10}       /etc/php/7.2/fpm/pool.d/www.conf
mod_env "pm.min_spare_servers" ${pm_min_spare_servers:-10}   /etc/php/7.2/fpm/pool.d/www.conf
mod_env "pm.max_spare_servers" ${pm_max_spare_servers:-1280} /etc/php/7.2/fpm/pool.d/www.conf

# start nginx server
service nginx start

# Change storage folders owner.
chown www-data:www-data -R storage bootstrap/cache

# Start php-fpm server and initialize laravel app.
service php7.2-fpm start
php artisan storage:link
php artisan optimize
if [[ "`cat .env|grep ^APP_KEY=$`" != "" ]]; then  # Lack of APP_KEY
    yes|php artisan key:generate
fi
yes|php artisan migrate
php artisan optimize
php artisan lduoj:init

# Start laravel-queue
php artisan queue:work

# Sleep forever to keep container alives.
sleep infinity
