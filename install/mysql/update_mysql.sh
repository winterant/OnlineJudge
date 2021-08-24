#!/bin/sh

set -ex
APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))    #项目存放位置

# update mysql table schema
USER=$(cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}')
PASSWORD=$(cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}')
mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE IF NOT EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -Dlduoj_upgrade < ${APP_HOME}/install/mysql/lduoj.sql
php ${APP_HOME}/install/mysql/structure_sync.php ${USER} ${PASSWORD} | mysql -u${USER} -p${PASSWORD} -Dlduoj -v
mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
