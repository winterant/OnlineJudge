#!/bin/bash

set -e
sleep 5 # Waiting for mysql being started.

# If host machine has not files, give it files.
if [ ! -d "/app/public" ];then
    yes|cp -rf /app_tmp/. /app/
    rm -rf /app_tmp
fi

# work dir.
cd /app

# Receive arguments.
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-lduoj}
DB_USERNAME=${DB_USERNAME:-oj_user}
DB_PASSWORD=${DB_PASSWORD:-OurFutrue2045}
JUDGE0_HOST=${JUDGE0_HOST:-judge0-server}
JUDGE0_PORT=${JUDGE0_PORT:-2358}
URL_HTTPS=${URL_HTTPS:-false}
sed -i "s/^DB_HOST.*$/DB_HOST=${DB_HOST}/" .env
sed -i "s/^DB_PORT.*$/DB_PORT=${DB_PORT}/" .env
sed -i "s/^DB_DATABASE.*$/DB_DATABASE=${DB_DATABASE}/" .env
sed -i "s/^DB_USERNAME.*$/DB_USERNAME=${DB_USERNAME}/" .env
sed -i "s/^DB_PASSWORD.*$/DB_PASSWORD=${DB_PASSWORD}/" .env
sed -i "s/^JUDGE0_HOST.*$/JUDGE0_HOST=${JUDGE0_HOST}/" .env
sed -i "s/^JUDGE0_PORT.*$/JUDGE0_PORT=${JUDGE0_PORT}/" .env
sed -i "s/^URL_HTTPS.*$/URL_HTTPS=${URL_HTTPS}/" .env

# init. database
# The function of executing a mysql command.
function mysql_exec(){
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e"$1"
    return $?
}
# Checking mysql is connecting.
if mysql_exec "select version();"; then
    echo "[OK] MySQL is running."
else
    echo "[No] Mysql is not running. sleep a while to wait."
    sleep 5
    exit -1
fi
# Checking database with tables exists or not.
if mysql_exec "select count(*) from ${DB_DATABASE}.users;"; then
    echo "Databse ${DB_DATABASE}'s tables already exist."
    # echo "Updating structure..."
    # bash install/mysql/update_mysql.sh
else
    echo "Databse ${DB_DATABASE}'s tables do not exist. Creating..."
    mysql_exec "CREATE DATABASE IF NOT EXISTS lduoj;"
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -Dlduoj < install/mysql/lduoj.sql
fi

# start php-fpm
service php7.2-fpm start
php artisan key:generate
php artisan optimize

# start nginx
service nginx start

sleep infinity
