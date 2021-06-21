#!/bin/bash

APP_HOME=/home/LDUOnlineJudge
echo "Project location: ${APP_HOME}"

if [ ! -d /volume/LDUOnlineJudge ]; then
    mv -f "${APP_HOME}" /volume/
else
    rm -rf "${APP_HOME}"
fi
ln -s /volume/LDUOnlineJudge "${APP_HOME}"

if [ ! -f /volume/etc/mysql/debian.cnf ]; then
    cp /etc/mysql/debian.cnf /etc/mysql/debian.cnf.backup
    mkdir -p /volume/etc/mysql
    mv /etc/mysql/debian.cnf /volume/etc/mysql/
else
    rm -rf /etc/mysql/debian.cnf
fi
ln -s /volume/etc/mysql/debian.cnf /etc/mysql/debian.cnf

if [ ! -d /volume/mysql ]; then
    mv /var/lib/mysql /volume/
else
    rm -rf /var/lib/mysql
fi
ln -s /volume/mysql /var/lib/mysql
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
