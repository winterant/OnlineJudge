#!/bin/bash

APP_HOME=/home/LDUOnlineJudge

if [ ! -d /volume/LDUOnlineJudge ]; then
    mv -f "${APP_HOME}" /volume/
else
    rm -rf "${APP_HOME}"
fi
ln -s /volume/LDUOnlineJudge "${APP_HOME}"

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
