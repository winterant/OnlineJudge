#!/bin/bash

APP_HOME=/home/LDUOnlineJudge

if [ ! -d /volume/LDUOnlineJudge ]; then
    mv -f "${APP_HOME}" /volume/
else
    rm -rf "${APP_HOME}"
fi
ln -s /volume/LDUOnlineJudge "${APP_HOME}"


################################################################################
# 2021.06.29取消了除lduoj表以外的表映射到/volume，所以此处兼容以前的版本，把映射出去的表移回原位
if [ -h /var/lib/mysql ]; then
    rm -rf /var/lib/mysql
    mv -f /volume/mysql /var/lib/mysql
fi
if [ -h /etc/mysql/debian.cnf ]; then
    rm -rf /etc/mysql/debian.cnf
    mv -f /volume/etc/mysql/debian.cnf /etc/mysql/debian.cnf
    rm -rf /volume/etc
fi
################################################################################


if [ ! -d /volume/mysql/lduoj ]; then
    mv /var/lib/mysql/lduoj /volume/mysql/lduoj
else
    rm -rf /var/lib/mysql/lduoj
fi
ln -s /volume/mysql/lduoj /var/lib/mysql/lduoj
chown -R mysql:mysql /volume/mysql
rm -rf /var/run/mysqld/mysqld.sock.lock

chmod -R 755 "${APP_HOME}"/storage "${APP_HOME}"/bootstrap/cache
chown www-data:www-data -R "${APP_HOME}"/storage "${APP_HOME}"/bootstrap/cache
service nginx start
service php7.2-fpm start
service mysql start
bash "${APP_HOME}"/judge/startup.sh

while true; do
#    echo "Keep docker container running in the background!";
    sleep 10;
done
