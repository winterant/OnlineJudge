FROM --platform=linux/amd64 ubuntu:20.04

# Required software and their configs
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone &&\
    sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.163.com\/ubuntu/g' /etc/apt/sources.list &&\
    apt update && apt upgrade -y &&\
    apt install -y software-properties-common &&\
    echo -e '\n' | apt-add-repository ppa:ondrej/php &&\
    apt update && apt-get -y upgrade &&\
    apt install -y php7.2 php7.2-fpm php7.2-mysql \
        php7.2-xml php7.2-mbstring \
        php7.2-gd php7.2-curl php7.2-zip &&\
    apt install -y nginx mysql-client=8.0.* composer zip unzip language-pack-en-base

# Deploy laravel app
COPY . /app/

WORKDIR /app

RUN export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8 &&\
    composer install --ignore-platform-reqs &&\
    cp -rf .env.example .env &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    ln -s /app/nginx.conf /etc/nginx/conf.d/lduoj.conf &&\
    # docker entrypoint
    cp docker-entrypoint.sh /docker-entrypoint.sh &&\
    chmod +x /docker-entrypoint.sh &&\
    # Rename the project to prevent to conflict with existed `volume`
    mv /app /app_tmp

ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 80
