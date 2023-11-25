FROM ubuntu:22.04

# 安装运行环境
RUN set -ex &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.aliyun.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt update && apt upgrade -y &&\
    # php, composer
    apt install -y software-properties-common &&\
    yes | apt-add-repository ppa:ondrej/php &&\
    apt update && apt upgrade -y &&\
    apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis \
        php8.1-xml php8.1-mbstring \
        php8.1-gd php8.1-curl php8.1-zip \
        curl zip unzip &&\
    curl -sS https://getcomposer.org/installer | php &&\
    mv composer.phar /usr/bin/composer &&\
    # supervisor
    apt install -y supervisor &&\
    # nginx, mysql-client, ...
    apt install -y nginx mysql-client=8.0.* vim language-pack-en-base &&\
    export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8

# 拷贝项目源码
COPY . /app_src/

# 安装代码查重工具sim
RUN cd /app_src &&\
    apt update &&\
    apt install -y gcc make flex &&\
    cp -rf install/sim/sim.1 /usr/share/man/man1/ &&\
    cd install/sim/ && make install &&\
    apt autoremove -y gcc make flex

# 安装laravel依赖
RUN cd /app_src &&\
    cp -rf .env.example .env &&\
    composer install --ignore-platform-reqs --no-dev -o &&\
    composer dump-autoload --optimize --no-dev

# 部署配置文件
RUN cd /app_src &&\
    # docker entrypoint
    cp install/docker/entrypoint.sh /docker-entrypoint.sh &&\
    chmod +x /docker-entrypoint.sh &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf &&\
    sed -i "s/worker_connections [0-9]*;$/worker_connections 51200;/" /etc/nginx/nginx.conf &&\
    # supervisor
    cp install/supervisor/* /etc/supervisor/conf.d/ &&\
    # php.ini; open php extension, increase post size.
    sed -i "/^;extension=gettext.*/i extension=gd"    /etc/php/8.1/fpm/php.ini &&\
    sed -i "/^;extension=gettext.*/i extension=curl"  /etc/php/8.1/fpm/php.ini &&\
    sed -i "/^;extension=gettext.*/i extension=zip"   /etc/php/8.1/fpm/php.ini &&\
    sed -i "/^;extension=gettext.*/i extension=redis" /etc/php/8.1/fpm/php.ini &&\
    sed -i "s/^.\?post_max_size\s\?=.*$/post_max_size=4096M/" /etc/php/8.1/fpm/php.ini &&\
    sed -i "s/^.\?upload_max_filesize\s\?=.*$/upload_max_filesize=4096M/" /etc/php/8.1/fpm/php.ini &&\
    # php-fpm.conf
    sed -i "s/^.\?error_log\s\?=.*$/error_log=\/app\/storage\/logs\/php-fpm\/php-fpm.log/" /etc/php/8.1/fpm/php-fpm.conf &&\
    # php-fpm/pool.d/www.conf
    sed -i "s/^.\?pm.status_path\s\?=.*$/pm.status_path=\/fpm-status/" /etc/php/8.1/fpm/pool.d/www.conf &&\
    # Done.
    echo Done.

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 80
