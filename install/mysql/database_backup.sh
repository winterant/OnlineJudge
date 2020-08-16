#!/bin/sh

if [ -n "$1" ]; then
    back_dir=.
else
    back_dir=$1
fi

USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysqldump -u${USER} -p${PASSWORD} -B lduoj > ${back_dir}/lduoj_backup.sql

echo "Generated back-up: ${back_dir}/lduoj_backup.sql"
