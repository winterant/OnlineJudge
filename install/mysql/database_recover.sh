#!/bin/sh

APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))
sql_path=${APP_HOME}/install/mysql/lduoj.sql

USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -Dlduoj < "${sql_path}"

echo "Recovered from ${sql_path}"
