#!/bin/bash

set -ex


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
# Change project owner.
chown -R www-data:www-data bootstrap storage

php artisan storage:link
php artisan optimize
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
php artisan lduoj:init


##########################################################################
# Background running
##########################################################################
bash storage/logs/nginx/auto-clear-log.sh 2>&1 &


##########################################################################
# Start laravel-queue.
##########################################################################
mod_env "numprocs" ${JUDGE_MAX_RUNNING:-$[(`nproc`+1)/2]} /etc/supervisor/conf.d/judge-queue.conf
supervisord                # Start up supervisor
supervisorctl update       # Detect changes to existing config files
supervisorctl start all    # Start all processes
supervisorctl status all   # Display running status


##########################################################################
# Sleep forever to keep container alive.
##########################################################################
sleep infinity
