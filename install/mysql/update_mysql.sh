#!/bin/sh

set -ex
APP_HOME=$(dirname $(dirname $(dirname $(readlink -f "$0"))))    #项目存放位置

# update mysql table schema
USER=$(cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}')
PASSWORD=$(cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}')

# mysql >= 8.0 由于默认用户密码方式不对，需重新设一下密码，php才能连接
mysql -u${USER} -p${PASSWORD} -e"alter user '${USER}'@'localhost' IDENTIFIED WITH mysql_native_password BY '${PASSWORD}';"

mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE IF NOT EXISTS lduoj_upgrade;"
mysql -u${USER} -p${PASSWORD} -Dlduoj_upgrade < ${APP_HOME}/install/mysql/lduoj.sql
php ${APP_HOME}/install/mysql/structure_sync.php ${USER} ${PASSWORD} | mysql -u${USER} -p${PASSWORD} -Dlduoj_db -v
mysql -u${USER} -p${PASSWORD} -e"DROP DATABASE IF EXISTS lduoj_upgrade;"
