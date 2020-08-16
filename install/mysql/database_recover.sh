#!/bin/sh

if [ -n "$1" ]; then
    echo "Please point sql path: bash database_recover.sh [your sql path]"
    exit 1;
else
    backup=$1
fi

USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysql -u${USER} -p${PASSWORD} -Dlduoj < ${backup}
