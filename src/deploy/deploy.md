# 安装维护

## 🍷 准备工作

- 安装`docker`（若已安装请跳过）；[参考文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)
  ```bash
  # 0. 先清除与docker相关的残余软件包（适用于多次安装docker失败的情况）
  sudo apt remove docker docker-engine docker.io containerd runc
  # 1. 使用官方脚本安装docker
  apt update
  sudo curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun
  # 2. 启动docker服务
  sudo systemctl enable docker
  sudo systemctl start docker
  # 3. 查看版本以验证是否安装成功
  sudo docker version
  ```
- 如遇拉取镜像总是失败或超时，可**更换docker镜像源**。  
  编辑文件`/etc/docker/daemon.json`：
  ```shell
  sudo vim /etc/docker/daemon.json
  ```
  按`i`进入编辑模式，  在文件中输入以下内容：
  ```json
  {
    "registry-mirrors": [
      "https://registry.docker-cn.com",
      "https://docker.mirrors.ustc.edu.cn",
      "https://ustc-edu-cn.mirror.aliyuncs.com",
      "https://hub-mirror.c.163.com",
      "https://mirror.baidubce.com"
    ]
  }
  ```
  按`Esc`键退出编辑模式，并输入命令`:wq`保存文件。  
  重启docker让配置生效：
  ```bash
  sudo systemctl daemon-reload
  sudo systemctl restart docker
  ```

## 🔨 部署

#### 1. 获取部署脚本
```bash
# 1. 创建项目文件夹并进入
mkdir OnlineJudge
cd OnlineJudge
# 2. 下载部署脚本和配置文件, 注意-O是大写字母O.
curl -O https://gitee.com/winant/OnlineJudge/raw/master/install/docker-compose.yml
curl -O https://gitee.com/winant/OnlineJudge/raw/master/install/lduoj.conf
```

#### 2. 启动服务
```bash
sudo docker compose up -d
```

- 访问首页`http://ip:8080`(该端口在`docker-compose.yml`中配置)；可在宿主机[配置域名](/deploy/network.md)；
- 默认管理员用户：`admin`，默认密码`adminadmin`，务必更改默认密码；

## 🚗 更新升级

> 升级原理：
> 1. 将部署脚本`docker-compose.yml`、`lduoj.conf`的内容更新到[最新](https://github.com/winterant/OnlineJudge/tree/master/install)，这一步完全可以手动修改文件而不采用下文的命令升级方式。
> 2. 修改`lduoj.conf`中必要的配置项，如[邮箱配置](./email.md)。
> 3. 重新启动服务即可完成升级。

#### 0. 查看镜像最新版本号（可跳过）

查看网页端最新版本号（或[前往网页](https://hub.docker.com/r/winterant/lduoj/tags)查看）：
```bash
curl -s "https://registry.hub.docker.com/v2/repositories/winterant/lduoj/tags?page_size=5" |jq ".results[].name"
```

各镜像版本依赖关系：
| `winterant/lduoj` | `winterant/judge` | `mysql` | `redis` |
| ----------------- | ----------------- | ------- | ------- |
| 1.1 ~ 1.8         | 1.2               | 8.0     | 7.0     |

| `winterant/lduoj` | `winterant/go-judge` | `mysql` | `redis` |
| ----------------- | -------------------- | ------- | ------- |
| 2.0～最新         | 1.0                  | 8.0     | 7.0     |

#### 1. 升级之前，先停止服务，并备份数据
```bash
cd OnlineJudge            # 进入项目文件夹
sudo docker compose down  # 停止服务
```
将整个项目文件夹打包备份：
```bash
cd ..   # 要在项目文件夹 OnlineJudge/ 的上一级目录执行备份
tar -cf - ./OnlineJudge | pigz -p $(nproc) > lduoj.tar.gz
```
务必备份！因不备份造成的数据损失，后果自负。

#### 2. 获取最新部署脚本（会直接覆盖旧文件）
```bash
cd OnlineJudge  # 进入项目文件夹
# 下载部署脚本和配置文件, 注意-O是大写字母O.
curl -O https://gitee.com/winant/OnlineJudge/raw/master/install/docker-compose.yml
curl -O https://gitee.com/winant/OnlineJudge/raw/master/install/lduoj.conf
```

#### 3. 拉取最新镜像，并启动服务
```bash
# sudo docker compose pull    # 将根据docker-compose.yml拉取所有最新镜像
sudo docker compose pull web  # 仅更新lduoj-web镜像，大多数版本只升级web，可参考上文<各镜像版本依赖关系>
sudo docker compose up -d     # 启动服务
```

## 💿 备份/迁移

### 备份
将整个项目文件夹打包备份：
```bash
# 注意是在项目文件夹 OnlineJudge/ 的外层执行备份
tar -cf - ./OnlineJudge | pigz -p $(nproc) > lduoj20230623.tar.gz
```

### 恢复
#### 1. 解压备份包
```bash
tar -zxvf lduoj20230623.tar.gz  # 解压
mv lduoj20230623 OnlineJudge    # 项目文件夹改一下名字
```
#### 2. 启动服务
```bash
cd OnlineJudge             # 进入项目文件夹
sudo docker compose up -d  # 启动服务
```
