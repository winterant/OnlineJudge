#!/bin/bash

set -ex
sleep 5 # Waiting for mysql being started.


##########################################################################
# Get App Files
##########################################################################
# If host machine has not files, give it files.
if [ ! -d "/app/app" ];then
    echo "Copying files from /app_src to /app"
    yes|cp -rf /app_src/. /app/
fi


##########################################################################
# Configuration
##########################################################################
function mod_env(){
    sed -i "s/^.\?$1\s\?=.*$/$1=${2//\//\\\/}/" $3
}
mod_env "TIMEZONE"          ${TZ:-Asia/Shanghai}                .env
mod_env "APP_DEBUG"         ${APP_DEBUG:-false}                 .env
mod_env "HREF_FORCE_HTTPS"  ${HREF_FORCE_HTTPS:-false}          .env
mod_env "DB_HOST"           ${MYSQL_HOST:-host.docker.internal} .env
mod_env "DB_PORT"           ${MYSQL_PORT:-3306} .env
mod_env "DB_DATABASE"       ${MYSQL_DATABASE}   .env
mod_env "DB_USERNAME"       ${MYSQL_USER}       .env
mod_env "DB_PASSWORD"       ${MYSQL_PASSWORD}   .env
mod_env "REDIS_HOST"        ${REDIS_HOST:-host.docker.internal} .env
mod_env "REDIS_PORT"        ${REDIS_PORT:-6379} .env
mod_env "REDIS_PASSWORD"    ${REDIS_PASSWORD}   .env

########### config php
php_config_file=/etc/php/8.1/fpm/php.ini
# open php extension
sed -i "/^;extension=gettext.*/i extension=gd"    ${php_config_file}
sed -i "/^;extension=gettext.*/i extension=curl"  ${php_config_file}
sed -i "/^;extension=gettext.*/i extension=zip"   ${php_config_file}
sed -i "/^;extension=gettext.*/i extension=redis" ${php_config_file}

# file size
mod_env "post_max_size"        ${php_post_max_size:-64M}       ${php_config_file}
mod_env "upload_max_filesize"  ${php_upload_max_filesize:-64M} ${php_config_file}

########## config php-fpm
mod_env "error_log" /app/storage/logs/php-fpm.log /etc/php/8.1/fpm/php-fpm.conf

########### config php-fpm pool
php_fpm_config_file=/etc/php/8.1/fpm/pool.d/www.conf
# default php-fpm `pm` for server with 32GB max memory.
mod_env "pm"                   ${fpm_pm:-dynamic}                ${php_fpm_config_file}
mod_env "pm.max_children"      ${fpm_pm_max_children:-1024}      ${php_fpm_config_file}
# The following item is avaliable only if pm=dynamic.
mod_env "pm.start_servers"     ${fpm_pm_start_servers:-16}       ${php_fpm_config_file}
mod_env "pm.min_spare_servers" ${fpm_pm_min_spare_servers:-8}    ${php_fpm_config_file}
mod_env "pm.max_spare_servers" ${fpm_pm_max_spare_servers:-1024} ${php_fpm_config_file}
# php-fpm will be recreated after has processed for `pm.max_request` times.
mod_env "pm.max_requests"      ${fpm_pm_max_requests:-1000}      ${php_fpm_config_file}
# Set pm.status_path which is the URI of php-fpm status page. Must be started with /
mod_env "pm.status_path"  "/fpm-status"  ${php_fpm_config_file}

########### nginx config
sed -i "s/worker_connections [0-9]*;$/worker_connections 51200;/" /etc/nginx/nginx.conf


##########################################################################
# Initialize laravel app.
##########################################################################
php artisan storage:link
php artisan optimize
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
php artisan lduoj:init

# Change project owner.
chown -R www-data:www-data .


##########################################################################
# Start Server
##########################################################################
# start nginx server
echo "Start" >> /app/storage/logs/nginx/access.log
echo "Start" >> /app/storage/logs/nginx/error.log
service nginx start

# Start php-fpm server
service php8.1-fpm start


##########################################################################
# Background running
##########################################################################
bash storage/logs/nginx/auto-clear-log.sh 2>&1 &


##########################################################################
# Start laravel-queue. Although there are more than one queue they still execute one by one
##########################################################################
php artisan queue:work --queue=default,CorrectSubmittedCount

# Sleep forever to keep container alives.
sleep infinity
