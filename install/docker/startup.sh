#!/bin/bash

##############################################################
# 为了持久化数据，本脚本将在容器启动时，将必要的数据移动到/volume
# 在创建容器时，请将/volume挂载到宿主机
# 需要持久化的数据包括：
# - /home/LDUOnlineJudge/storage             # 项目主要数据
# - /home/LDUOnlineJudge/.env                # 项目配置文件
# - /home/LDUOnlineJudge/judge/config.sh     # 判题端配置文件
# - /home/LDUOnlineJudge/public/favicon.ico  # 网站图标
# - /etc/php/7.2/fpm/pool.d/www.conf         # php7.2-fpm配置文件
# - /var/lib/mysql/lduoj                     # 数据库lduoj
# 为了保证系统运行，移动后的文件需要软连接回原位置
##############################################################


transferFile(){
    # 将$1转移到$2, 并软连接回去
    if [ ! -d $2 ]; then
        mkdir -p $(dirname $2)
        mv -f $1 $2
    else
        rm -rf $1
    fi
    ln -s $2 $1
}

transferFile /home/LDUOnlineJudge/storage            /volume/LDUOnlineJudge/storage
transferFile /home/LDUOnlineJudge/.env               /volume/LDUOnlineJudge/.env
transferFile /home/LDUOnlineJudge/judge/config.sh    /volume/LDUOnlineJudge/judge/config.sh
transferFile /home/LDUOnlineJudge/public/favicon.ico /volume/LDUOnlineJudge/public/favicon.ico
transferFile /etc/php/7.2/fpm/pool.d/www.conf        /volume/php-fpm/www.conf
transferFile /var/lib/mysql/lduoj                    /volume/mysql/lduoj
chown -R mysql:mysql /volume/mysql
rm -rf /var/run/mysqld/mysqld.sock.lock

service nginx start
service php7.2-fpm start
service mysql start
bash /home/LDUOnlineJudge/judge/startup.sh

sleep infinity
