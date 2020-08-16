#!/bin/sh

USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysqldump -u${USER} -p${PASSWORD} -B lduoj > $(dirname $0)/lduoj.sql

echo "Generated back-up: $(dirname $0)/lduoj.sql"
