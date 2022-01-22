#!/bin/bash

transferFile(){
    # 将$1转移到$2, 并软连接回去
    if [ ! -e $2 ]; then
        mkdir -p $(dirname $2)
        mv -f $1 $2
    else
        rm -rf $1
    fi
    ln -s $2 $1
}

transferFile /home/LDUOnlineJudge              /volume/LDUOnlineJudge
transferFile /etc/php/7.2/fpm/pool.d/www.conf  /volume/php-fpm/www.conf
transferFile /var/lib/mysql/lduoj              /volume/mysql/lduoj

chown -R www-data:www-data /home/LDUOnlineJudge/bootstrap/cache/
chown -R www-data:www-data /home/LDUOnlineJudge/storage/
chown -R mysql:mysql /var/lib/mysql/
rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
cd /home/LDUOnlineJudge
php artisan optimize
bash /home/LDUOnlineJudge/judge/startup.sh

sleep infinity
