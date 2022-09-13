FROM --platform=linux/amd64 ubuntu:20.04

# Required software and their configs
RUN sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.163.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt update && apt upgrade -y &&\
    apt install -y software-properties-common &&\
    echo -e '\n' | apt-add-repository ppa:ondrej/php &&\
    apt update && apt-get -y upgrade &&\
    apt install -y php7.2 php7.2-fpm php7.2-mysql &&\
        php7.2-xml php7.2-mbstring &&\
        php7.2-gd php7.2-curl php7.2-zip &&\
    apt install -y nginx mysql-client=8.0.* composer zip unzip language-pack-en-base&&\
    sed -i "s/^;extension=gd.*/extension=gd/"                   /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^;extension=curl.*/extension=curl/"               /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^;extension=zip.*/extension=zip/"                 /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^post_max_size.*/post_max_size=128M/"             /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^upload_max_filesize.*/upload_max_filesize=128M/" /etc/php/7.2/fpm/php.ini &&\
    sed -i "s/^.\?pm.max_children.*/pm.max_children = 1000/"    /etc/php/7.2/fpm/pool.d/www.conf &&\
    sed -i "s/^.\?pm.max_spare_servers.*/pm.max_spare_servers = 500/" /etc/php/7.2/fpm/pool.d/www.conf

# Deploy laravel app
COPY . /app/

WORKDIR /app

RUN export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8 &&\
    composer install --ignore-platform-reqs &&\
    cp -rf .env.example .env &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf &&\
    # docker entrypoint
    cp install/docker/entrypoint.sh /entrypoint.sh &&\
    chmod +x /entrypoint.sh &&\
    # Rename the project to prevent to conflict with existed `volume`
    mv /app /app_tmp

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
