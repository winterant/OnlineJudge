# 安装维护

## 🍷 准备工作

1. 安装`docker`；[参考文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)
  ```bash
  sudo curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun
  # 启动docker
  sudo systemctl enable docker
  sudo systemctl start docker
  ```
2. 安装`docker-compose`；[参考文档](https://yeasy.gitbook.io/docker_practice/compose/install)
  ```bash
  sudo curl -L "https://github.com/docker/compose/releases/download/v2.2.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
  docker-compose version
  ```

## 🔨 一键部署

```bash
git clone -b deploy https://github.com/winterant/OnlineJudge.git
cd OnlineJudge
docker-compose up -d
```

- 访问首页`http://ip:8080`；可在宿主机[配置域名](/deploy/network.md)；
- 默认管理员用户：`admin`，默认密码`adminadmin`，务必更改默认密码；

## 🚗 升级

- 版本内更新(docker tag不变)
  ```
  docker-compose pull web judge-server
  docker-compose up -d
  ```
- 跨版本升级  
  务必提前备份！获取稳定版本[releases](https://github.com/winterant/LDUOnlineJudge/releases)，解压后进入文件夹，一键部署即可。

## 💿 备份/迁移

### 备份
1. 将`docker-compose.yml`所在文件夹打包备份；
    ```bash
    tar -cf - ./lduoj | pigz -p $(nproc) > lduoj_bak.tar.gz
    ```

### 恢复
1. 解压备份包
    ```bash
    tar -zxvf lduoj_bak.tar.gz
    ```
2. 一键部署
    ```bash
    cd lduoj_bak
    sudo docker-compose up -d
    ```
