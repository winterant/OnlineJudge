#!/bin/sh

web_home=/home    #项目存放位置

# 备份
mv -f ${web_home}/LDUOnlineJudge ${web_home}/lduoj_last_version

# 下载项目源码
apt update -y
apt install -y git
cd ${web_home} && git clone https://github.com/iamwinter/LDUOnlineJudge.git
cp -r -p -f ${web_home}/lduoj_last_version/storage ${web_home}/LDUOnlineJudge/storage
cp -r -p -f ${web_home}/lduoj_last_version/public/favicon.ico ${web_home}/LDUOnlineJudge/public/favicon.ico
cp -r -p -f ${web_home}/lduoj_last_version/.env ${web_home}/LDUOnlineJudge/.env
chmod -R 777 ${web_home}/LDUOnlineJudge/bootstrap/cache
apt remove -y git

apt install -y composer
cd ${web_home}/LDUOnlineJudge && composer install --ignore-platform-reqs

echo "You have successfully updated LDU Online Judge!"
echo "Enjoy it!"
echo "Installation location: ${web_home}/LDUOnlineJudge"
# delete self
rm -rf $0
