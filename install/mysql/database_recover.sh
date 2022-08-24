#!/bin/sh

if [ -n "$1" ]; then
    sql_path="$1"
else
    APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))
    sql_path=${APP_HOME}/install/mysql/lduojbackup.sql
fi

DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-lduoj}
DB_USERNAME=${DB_USERNAME:-oj_user}
DB_PASSWORD=${DB_PASSWORD:-OurFutrue2045}

mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -D"${DB_DATABASE}" < "${sql_path}"

echo "Recovered from ${sql_path}"
