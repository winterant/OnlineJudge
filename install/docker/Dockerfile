FROM ubuntu:20.04

# 不能在这把项目复制进/volume，否则启动容器时会被宿主机目录覆盖
COPY LDUOnlineJudge/ /home/LDUOnlineJudge/

RUN set -ex &&\
    #sed -i 's/\/\/.*\/ubuntu/\/\/mirrors.aliyun.com\/ubuntu/g' /etc/apt/sources.list && \
    ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime &&\
    echo 'Asia/Shanghai' > /etc/timezone

RUN bash /home/LDUOnlineJudge/install/ubuntu/install.sh

# 修改nginx配置文件的web root路径为/volume/LDUOnlineJudge/public
RUN sed -i "s/root .*\/.*;$/root \/volume\/LDUOnlineJudge\/public;/" /etc/nginx/conf.d/lduoj.conf

RUN cp /home/LDUOnlineJudge/install/docker/startup.sh / &&\
    chmod +x /startup.sh

ENTRYPOINT ["/startup.sh"]

VOLUME ["/volume"]

EXPOSE 80
