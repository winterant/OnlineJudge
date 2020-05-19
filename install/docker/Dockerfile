FROM ubuntu:16.04

RUN echo "start to install LDUOnlineJudge..."\
    && apt -y update && apt -y upgrade\
    && apt -y purge git\
    && apt -y install git\
    && git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge\
    && cd /home/LDUOnlineJudge\
    && cp -rf .env.example .env\
    && chmod -R 777 storage bootstrap/cache\
    && apt -y update && apt -y upgrade\
    && apt -y install software-properties-common python-software-properties\
    && echo -e "\n" | apt-add-repository ppa:ondrej/php\
    && apt -y update\
    && apt -y install php7.2 php7.2-fpm php7.2-mysql php7.2-xml\
    && service php7.2-fpm start\
    && apt -y install composer zip unzip\
    && composer install --ignore-platform-reqs\
    && php artisan storage:link\
    && php artisan key:generate\
    && php artisan optimize\
    && apt -y install nginx\
    && rm -rf /etc/nginx/sites-enabled/default\
    && cp -f /home/LDUOnlineJudge/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf\
    && service nginx restart\
    && apt -y install mysql-server\
    && service mysql restart\
    && USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`\
    && PASSWORD=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`\
    && mysql -u${USER} -p${PASSWORD} -e"CREATE DATABASE lduoj;"\
    && mysql -u${USER} -p${PASSWORD} -e"CREATE USER 'lduoj'@'localhost' IDENTIFIED WITH mysql_native_password BY '123456789';"\
    && mysql -u${USER} -p${PASSWORD} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'localhost' identified by '123456789';flush privileges;"\
    && mysql -u${USER} -p${PASSWORD} -Dlduoj < /home/LDUOnlineJudge/install/mysql/lduoj.sql\
    && echo 'www-data ALL = NOPASSWD: ALL' >> /etc/sudoers\
    && bash /home/LDUOnlineJudge/judge/install.sh\
    && bash /home/LDUOnlineJudge/judge/startup.sh

EXPOSE 80
