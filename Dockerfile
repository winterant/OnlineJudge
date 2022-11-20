FROM --platform=linux/amd64 ubuntu:22.04

# Required software and their configs
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    apt update && apt upgrade -y &&\
    apt install -y software-properties-common &&\
    yes | apt-add-repository ppa:ondrej/php &&\
    apt update && apt upgrade -y &&\
    apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis \
        php8.1-xml php8.1-mbstring \
        php8.1-gd php8.1-curl php8.1-zip &&\
    apt install -y nginx mysql-client=8.0.* curl zip unzip language-pack-en-base &&\
    curl -sS https://getcomposer.org/installer | php &&\
    mv composer.phar /usr/bin/composer &&\
    export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8

# Deploy laravel application.
COPY . /app_src/

RUN cd /app_src &&\
    cp -rf .env.example .env &&\
    composer install --ignore-platform-reqs &&\
    composer dump-autoload --optimize &&\
    # docker entrypoint
    cp docker-entrypoint.sh /docker-entrypoint.sh &&\
    chmod +x /docker-entrypoint.sh &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp nginx-lduoj.conf /etc/nginx/conf.d/lduoj.conf &&\
    # version
    mkdir -p storage/app &&\
    echo 1.3.$(date "+%Y%m%d") > storage/app/.version

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 80
