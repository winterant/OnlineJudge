#!/bin/bash

mkdir -p /volume

# Project "LDUOnlineJudge" will be transfered to "volume"
# at the first time the container is started.
if [ ! -d /volume/LDUOnlineJudge ]; then
    mv -f /home/LDUOnlineJudge /volume/
else
    rm -rf /home/LDUOnlineJudge
fi

# php-fpm configuration
fpm_www=/etc/php/7.2/fpm/pool.d/www.conf
if [ ! -d /volume/php-fpm ]; then
    mkdir -p /volume/php-fpm
    mv -f $fpm_www /volume/php-fpm/www.conf
else
    rm -rf $fpm_www
fi
ln -s /volume/php-fpm/www.conf $fpm_www

# mysql
database=/var/lib/mysql/lduoj
if [ ! -d /volume/mysql/lduoj ]; then
    mkdir -p /volume/mysql
    mv -f $database /volume/mysql/
else
    rm -rf $database
fi
ln -s /volume/mysql/lduoj $database


cd /volume/LDUOnlineJudge

chown -R www-data:www-data bootstrap/cache storage
chown -R mysql:mysql /var/lib/mysql/
rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
php artisan storage:link
php artisan key:generate
php artisan optimize
bash judge/startup.sh

sleep infinity
