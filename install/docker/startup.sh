#!/bin/bash

mkdir -p /volume

# Project "LDUOnlineJudge" will be transfered to "volume"
# at the first time the container is started.
if [ -d /home/LDUOnlineJudge ]; then
    if [ -d /volume/LDUOnlineJudge ]; then # update code.
        rm -rf /home/LDUOnlineJudge/.env
        rm -rf /home/LDUOnlineJudge/public/favicon.ico
        rm -rf /home/LDUOnlineJudge/judge/config.sh
        cp -rf /home/LDUOnlineJudge /volume/
        rm -rf /home/LDUOnlineJudge
    else  # move entire project.
        mv -f /home/LDUOnlineJudge /volume/
    fi
fi

# php-fpm configuration
fpm_www=/etc/php/7.2/fpm/pool.d/www.conf
if [ ! -d /volume/php-fpm ]; then
    mkdir -p /volume/php-fpm
    mv -f $fpm_www /volume/php-fpm/www.conf
fi
rm -rf $fpm_www
ln -s /volume/php-fpm/www.conf $fpm_www

# mysql
database=/var/lib/mysql/lduoj
if [ ! -d /volume/mysql/lduoj ]; then
    mkdir -p /volume/mysql
    mv -f $database /volume/mysql/
fi
rm -rf $database
ln -s /volume/mysql/lduoj $database


cd /volume/LDUOnlineJudge

chown -R www-data:www-data bootstrap/cache storage
chown -R mysql:mysql /var/lib/mysql/
rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
php artisan optimize
bash judge/startup.sh

sleep infinity
