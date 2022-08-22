<h1 align="center">开发者文档</h1>

# 🧱 整体架构

+ `routes/web.php`：路由转发文件，定义了全站路由。
+ `config/oj/`：含本OJ自定义的配置文件。
+ `app/Http/`：后端控制器`Controllers`、中间件`Middleware`等程序。
+ `resources/views/`：前端html代码。
+ `resources/lang/`：网页文字语言翻译文件。
+ `public/`：网页访问入口`index.php`，js、css文件和web插件。
+ `storage/app/`：保存题目数据、文件等。
+ `storage/app/public/`：保存静态文件(如图片)等。软连接到`public/storage`供网页访问。
+ `judge/`：判题程序，与laravel框架无关。
+ `install/`：用于安装本OJ，与laravel框架无关。
+ `.env.example`：配置文件，含数据库连接信息、判题设置等。复制为`.env`生效。

# 💻 本地开发

## 方式一：基于docker

### 1. 启动容器

  ```shell
  docker run -d -p 8080:80 -p 8036:3306 -v /d/volume:/volume --name lduoj winterant/lduoj:22.08
  ```

+ `-p`指定8036端口作为宿主机mysql端口，指定8080端口作为网页入口。
+ `-v`将数据映射到本地`D:/volume/LDUOnlineJudge`，本地编辑项目即可。
+ 浏览器访问`http://localhost:8080`显示主页则表示部署成功。

### 2. 连接docker内的mysql数据库（非必需，等同于远程连接mysql）

  ```shell
  # 进入docker容器内
  docker exec -it lduoj /bin/bash

  # 修改mysql配置，允许任意主机访问
  sed -i 's/^bind-address.*$/bind-address=0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
  service mysql restart

  # 新建允许外部登录的mysql用户：'ldu'@'%'，密码123456。 **切勿与我相同或过于简单！**
  USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
  PW=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
  mysql -u${USER} -p${PW} -e"CREATE USER If Not Exists 'ldu'@'%' IDENTIFIED WITH mysql_native_password BY '123456';"
  mysql -u${USER} -p${PW} -e"GRANT all privileges ON lduoj.* TO 'ldu'@'%';flush privileges;"
  ```

然后远程连接【**宿主机ip**:8036】，使用新建的用户ldu登录mysql即可。

## 方式二：基于本地环境

### 1. 下载源码

  ```shell script
  git clone https://github.com/winterant/LDUOnlineJudge.git
  ```

### 2. 准备环境

+ PHP >=7.2 （必需拓展：php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring）
+ mysql >=5.7 （建库脚本：`install/mysql/lduoj.sql`）
+ 判题环境需求（只能在linux系统运行）：
    g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex

### 3. 配置文件

  ```bash
  cp .env.example .env
  cp judge/config.sh.sample judge/config.sh
  cp public/favicon.ico.sample public/favicon.ico
  ```

### 4. 初始化项目

  ```bash
  chown -R www-data:www-data storage bootstrap/cache  # linux系统需要赋权
  composer install --ignore-platform-reqs             # 下载laravel依赖

  php artisan storage:link    # 将静态目录软连接到public/storage
  php artisan key:generate    # 必需，生成.env中的APP_KEY
  php artisan optimize        # 优化汇总所有配置；开发阶段可不执行
  ```

### 5. 启动服务，预览主页。

  ```bash
  php -S 127.0.0.1:8000  # 或 php artisan serve --port=8000
  ```

浏览器访问`http://localhost:8000`显示主页则表示环境搭建成功。

# 🌏 Docker镜像发布

安装docker请参考[官方文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)

+ 将本项目构建为docker镜像
  
  ```bash
  git clone https://github.com/winterant/LDUOnlineJudge.git
  cd LDUOnlineJudge
  docker build -f install/docker/Dockerfile -t lduoj .  # 末尾有点
  ```
  注意：Windows系统下请先将换行符`\r\n`统一转换为`\n`再构建镜像。

+ 为镜像重命名

  ```bash
  docker tag lduoj winterant/lduoj
  ```

+ 将镜像发布到`dockerhub`

  ```bash
  docker push winterant/lduoj
  ```
