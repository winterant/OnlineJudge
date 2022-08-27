FROM winterant/lduoj_env:2.0

COPY . /app/

WORKDIR /app

RUN export LC_ALL=en_US.UTF-8 &&\
    export LANG=en_US.UTF-8 &&\
    composer install --ignore-platform-reqs &&\
    cp -rf .env.example .env &&\
    # nginx
    rm -rf /etc/nginx/sites-enabled/default &&\
    ln -s /app/install/nginx/lduoj.conf /etc/nginx/conf.d/lduoj.conf &&\
    # docker startup
    cp -rf install/docker/docker-startup.sh /docker-startup.sh &&\
    chmod +x /docker-startup.sh &&\
    # Rename the project to prevent to conflict with existed `volume`
    mv /app /app_tmp

VOLUME ["/app"]

ENTRYPOINT ["/docker-startup.sh"]

EXPOSE 80
