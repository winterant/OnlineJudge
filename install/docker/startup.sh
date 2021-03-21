#!/bin/bash

if [ ! -d /volume/LDUOnlineJudge ]; then
    mv /home/LDUOnlineJudge /volume/
else
    rm -rf /home/LDUOnlineJudge
fi
ln -s /volume/LDUOnlineJudge /home/LDUOnlineJudge

if [ ! -d /volume/mysql ]; then
    mv /var/lib/mysql /volume/
else
    rm -rf /var/lib/mysql
fi
ln -s /volume/mysql /var/lib/mysql
chown -R mysql:mysql /volume/mysql
rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
bash /home/LDUOnlineJudge/judge/startup.sh

while true; do
    echo "Keep docker container running in the background!";
    sleep 1;
done
