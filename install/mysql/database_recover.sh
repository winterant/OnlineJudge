#!/bin/sh

USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -Dlduoj < $(dirname $0)/lduoj.sql

echo "Recovered from $(dirname $0)/lduoj.sql"
