#!/bin/bash

set -ex


##########################################################################
# Set Time Zone
##########################################################################
ln -sf /usr/share/zoneinfo/${TZ:-Asia/Shanghai} /etc/localtime
echo '${TZ:-Asia/Shanghai}' > /etc/timezone


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
# Function implementation to modify file configuration items
# `mod_env <filepath> <key> <value?>`
function mod_env(){
    sed -i "s/^.\?$2\s\?=.*$/$2=${3//\//\\\/}/" $1
}

########### laravel .env
mod_env .env "APP_DEBUG"         ${APP_DEBUG:-false}
mod_env .env "HREF_FORCE_HTTPS"  ${HREF_FORCE_HTTPS:-false}
mod_env .env "JUDGE_SERVER"      ${JUDGE_SERVER:-"go-judge:5050"}
mod_env .env "DB_CONNECTION"     ${DB_CONNECTION:-mysql}
mod_env .env "DB_HOST"           ${DB_HOST:-mysql}
mod_env .env "DB_PORT"           ${DB_PORT:-3306}
mod_env .env "DB_DATABASE"       ${DB_DATABASE:-lduoj}
mod_env .env "DB_USERNAME"       ${DB_USERNAME:-oj_user}
mod_env .env "DB_PASSWORD"       ${DB_PASSWORD:-OurFuture2045}
mod_env .env "REDIS_HOST"        ${REDIS_HOST:-redis}
mod_env .env "REDIS_PORT"        ${REDIS_PORT:-6379}
mod_env .env "REDIS_PASSWORD"    ${REDIS_PASSWORD:-YourRedisPassword2012}
mod_env .env "MAIL_MAILER"       ${MAIL_MAILER:-smtp}
mod_env .env "MAIL_HOST"         ${MAIL_HOST:-smtp.qq.com}
mod_env .env "MAIL_PORT"         ${MAIL_PORT:-465}
mod_env .env "MAIL_ENCRYPTION"   ${MAIL_ENCRYPTION:-ssl}
mod_env .env "MAIL_USERNAME"     ${MAIL_USERNAME}
mod_env .env "MAIL_PASSWORD"     ${MAIL_PASSWORD}
mod_env .env "MAIL_FROM_ADDRESS" ${MAIL_FROM_ADDRESS}
mod_env .env "MAIL_FROM_NAME"    ${MAIL_FROM_NAME:-LDUOJ}

########### config php-fpm pool
fpm=/etc/php/8.1/fpm/pool.d/www.conf
mod_env ${fpm} "pm"                   ${fpm_pm:-dynamic}
mod_env ${fpm} "pm.max_children"      ${fpm_pm_max_children:-1024}
mod_env ${fpm} "pm.start_servers"     ${fpm_pm_start_servers:-16}
mod_env ${fpm} "pm.min_spare_servers" ${fpm_pm_min_spare_servers:-8}
mod_env ${fpm} "pm.max_spare_servers" ${fpm_pm_max_spare_servers:-1024}
mod_env ${fpm} "pm.max_requests"      ${fpm_pm_max_requests:-1000}


##########################################################################
# Start Server
##########################################################################
# start nginx server
echo "Start at" $(date "+%Y-%m-%d %H:%M:%S") >> /app/storage/logs/nginx/access.log
echo "Start at" $(date "+%Y-%m-%d %H:%M:%S") >> /app/storage/logs/nginx/error.log
service nginx start

# Start php-fpm server
echo "Start at" $(date "+%Y-%m-%d %H:%M:%S") >> /app/storage/logs/php-fpm.log
service php8.1-fpm start


##########################################################################
# Initialize laravel app.
##########################################################################

# Initialize laravel configuration.
php artisan optimize
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
chown -R www-data:www-data bootstrap storage
php artisan storage:link
php artisan lduoj:init


##########################################################################
# Background running
##########################################################################
bash storage/logs/nginx/auto-clear-log.sh 2>&1 &
bash storage/logs/queue/auto-clear-log.sh 2>&1 &


##########################################################################
# Start laravel-queue.
##########################################################################
mod_env /etc/supervisor/conf.d/judge-queue.conf \
    "numprocs" ${JUDGE_MAX_RUNNING:-$[(`nproc`+1)/2]}
supervisord                # Start up supervisor
supervisorctl update       # Detect changes to existing config files
supervisorctl start all    # Start all processes
supervisorctl status all   # Display running status


##########################################################################
# Sleep forever to keep container alive.
##########################################################################
sleep infinity
