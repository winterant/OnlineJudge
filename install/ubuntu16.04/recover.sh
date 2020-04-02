#!/bin/sh

set -x
if [ -n "$1" ]; then
    read -p "Please input absolute position of backup directory: " back_dir
else
    back_dir=$1
fi

# project with data and files
rm -rf /home/LDUOnlineJudge
cp -rfp ${back_dir}/LDUOnlineJudge /home/

# mysql
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -Dlduoj < ${back_dir}/lduoj.sql

# nginx
cp -rfp ${back_dir}/lduoj.nginx.conf  /etc/nginx/conf.d/lduoj.conf


echo -e "\nYou have successfully recoveried LDU Online Judge!"
echo -e "Enjoy it!\n"
