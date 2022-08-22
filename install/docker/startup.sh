#!/bin/bash

# start mysql
rm -rf /var/run/mysqld/mysqld.sock.lock
service mysql start

# start php-fpm
service php7.2-fpm start
cd /LDUOnlineJudge
if [[ "`cat /LDUOnlineJudge/.env|grep ^APP_KEY=`" == "APP_KEY=" ]];then
    php artisan key:generate
fi
php artisan optimize

# start nginx
service nginx start

sleep infinity
