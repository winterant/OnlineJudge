#!/bin/bash

rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
bash /home/LDUOnlineJudge/judge/startup.sh

while true; do
    echo "Keep docker container running in the background!";
    sleep 1;
done
