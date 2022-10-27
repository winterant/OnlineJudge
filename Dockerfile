FROM --platform=linux/amd64 ubuntu:20.04

# Required software and their configs
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    apt update && apt upgrade -y &&\
    apt install -y software-properties-common &&\
    yes | apt-add-repository ppa:ondrej/php &&\
    apt update && apt upgrade -y &&\
    apt install -y php7.2 php7.2-fpm \
        php7.2-mysql php7.2-redis \
        php7.2-xml php7.2-mbstring \
        php7.2-gd php7.2-curl php7.2-zip &&\
    apt install -y nginx mysql-client=8.0.* composer zip unzip language-pack-en-base &&\
    export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8

# Deploy laravel application.
COPY . /app_src/

RUN cd /app_src &&\
    cp -rf .env.example .env &&\
    composer install --ignore-platform-reqs &&\
    # docker entrypoint
    cp docker-entrypoint.sh /docker-entrypoint.sh &&\
    chmod +x /docker-entrypoint.sh &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    cp storage/scripts/nginx-lduoj.conf /etc/nginx/conf.d/lduoj.conf

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 80
