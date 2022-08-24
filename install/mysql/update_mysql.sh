#!/bin/sh

set -ex
APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))    #项目存放位置

if [ -n "$1" ]; then
    sql_path="$1"
else
    sql_path=${APP_HOME}/install/mysql/lduoj.sql
fi

# update mysql table schema
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-lduoj}
DB_USERNAME=${DB_USERNAME:-oj_user}
DB_PASSWORD=${DB_PASSWORD:-OurFutrue2045}

mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE IF NOT EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -Dlduoj_upgrade < ${sql_path}
php ${APP_HOME}/install/mysql/structure_sync.php ${DB_HOST} ${DB_PORT} ${DB_USERNAME} ${DB_PASSWORD} ${DB_DATABASE} | mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -Dlduoj -v
mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
