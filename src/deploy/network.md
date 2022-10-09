# 配置代理

网页端默认暴露80端口，并通过配置文件`docker-compose.yml`映射到宿主机8080端口。
你可以在宿主机配置网络代理，以实现域名访问，以及https证书配置。

## 📡 nginx

### 安装nginx

```bash
apt update
apt install -y nginx
```

`nginx`默认自带80端口配置文件，为避免冲突，在生产环境中可以删除它
```bash
rm /etc/nginx/sites-enabled/default
```

### 以http方式配置域名

1. 创建并编辑配置文件

```bash
vim /etc/nginx/conf.d/lduoj-http.conf
```

2. 输入内容

```
server {
    listen 80;
    server_name www.lduoj.com;  # 请替换为你的域名

    client_max_body_size 64m;   # 请求体大小上限

    location / {
        proxy_pass http://127.0.0.1:8080/;  # 代理web端口
        proxy_redirect off;
        proxy_set_header Host $host;  # 若监听非80端口,请改为$host:$server_port
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

3. 按`ESC`键，输入`:wq`并会车，即可保存配置文件。
4. 重启nginx使配置生效
```bash
sudo systemctl restart nginx
```

### 以https方式配置证书和域名

1. 创建并编辑配置文件

```bash
vim /etc/nginx/conf.d/lduoj-https.conf
```

2. 输入内容

```
server{
    listen 80;
    server_name lduoj.com;
    rewrite ^(.*)$  https://$host$1 permanent;  # 强制http转https
}

server {
    listen 443 ssl http2;
    server_name www.lduoj.com;  # 请替换为你的域名

    client_max_body_size 64m;   # 请求体大小上限

    # ssl的一些配置
    ssl_certificate conf.d/fullchain.crt;   # 请务必替换成你的ssl证书路径,相对于/etc/nginx/
    ssl_certificate_key conf.d/private.pem; # 同上
    ssl_protocols TLSv1.1 TLSv1.2;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:HIGH:!aNULL:!MD5:!RC4:!DHE;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    location / {
        proxy_pass http://127.0.0.1:8080/;
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

3. 按`ESC`键，输入`:wq`并会车，即可保存配置文件。
4. 重启nginx使配置生效
```bash
sudo systemctl restart nginx
```
