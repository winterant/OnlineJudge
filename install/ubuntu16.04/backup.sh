#!/bin/sh

web_home=/home    #项目存放位置
backup='lduoj_'$(date "+%Y%m%d_%H%M%S")

# 项目备份
if [ ! -d ${web_home}/lduoj_backup/${backup} ];then
  mkdir -p ${web_home}/lduoj_backup/${backup}
fi;
mv -f ${web_home}/LDUOnlineJudge ${web_home}/lduoj_backup/${backup}

# 数据库备份
USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
mysqldump -u${USER} -p${PASSWORD} -B lduoj > ${web_home}/lduoj_backup/${backup}/lduoj.sql

# nginx备份
cp -r -f -p /etc/nginx/conf.d/lduoj.conf ${web_home}/lduoj_backup/${backup}/lduoj.nginx.conf

echo -e "\nYou have successfully backuped LDU Online Judge!"
echo -e "Backup location: ${web_home}/lduoj_backup/${backup}/\n"
