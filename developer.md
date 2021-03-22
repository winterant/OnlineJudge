开发者笔记
===
> Hi,本文记录了整个Online Judge架构详细解析。

# 整体架构

+ 主要文件

  - `routes/web.php`：路由转发文件，定义了全站路由。
  - `config/oj/`：含本OJ自定义的配置文件。
  - `app/Http/`：后端控制器`Controllers`、中间件`Middleware`等程序。
  - `resources/views/`：前端html代码。
  - `resources/lang/`：网页文字语言文件。
  - `public/`：网页访问入口`index.php`，js、css文件和web插件。
  - `storage/app/`：保存题目数据、文件等。
  - `storage/app/public/`：保存静态文件(如图片)等。
     软连接到`public/storage`供网页访问。
  - `judge/`：判题程序，与laravel框架无关。
  - `install/`：用于安装本OJ，与laravel框架无关。
  - `.env.example`：配置文件，含数据库连接信息、判题设置等。
     复制为`.env`生效。

+ 工作原理

  网页端向用户展示题目等相关信息，用户可通过题目或竞赛途径上传自己的代码。

  判题机启动时，轮询程序(`judge/cpp/polling.cpp`)将不停查询数据库，
  收集未判的提交记录（提交编号），
  然后开启子进程(`judge/cpp/judge.cpp`)判题（最多并行5个）。
  对于每一个判题进程，主要步骤是：从数据库读取代码、编译、输入数据运行、
  输出结果与正确结果对比（或者特判）、将判题结论写入数据库。

# 本地二次开发

1. 下载源码

```shell script
git clone https://github.com/iamwinter/LDUOnlineJudge.git
cd LUDOnlineJudge
```

2. 准备环境

推荐`phpstudy`（含php、mysql、nginx等环境）。推荐IDE是`PhpStorm`。

请根据`LDUOnlineJudge/install/mysql/lduoj.sql`脚本创建数据库。

请根据`LDUOnlineJudge/install/nginx/lduoj.conf`配置好nginx。
或自行配置apache服务器。
切记网页入口文件是`/home/LDUOnlineJudge/public/index.php`

3. 填写配置文件

```shell script
cp -rf .env.example .env  # 使配置文件生效
```
然后编辑`.env`文件，填写mysql连接信息。

4. 初始化项目

```
mkdir -p storage/app/public    # 新建前端静态文件存储目录
chmod -R 777 storage bootstrap/cache  # linux必需！
composer install --ignore-platform-reqs  # 下载依赖

php artisan storage:link    # 将静态文件存储目录软连接到public/storage
php artisan key:generate    # 必需，生成.env中的APP_KEY
php artisan optimize        # 必需，加载并优化所有配置信息
```

5. 预览主页。

打开浏览器访问`http://localhost:port`显示主页则表示部署成功，
其中`port`请替换为nginx配置的端口号。

6. 开始愉快的编程。


# Docker操作笔记

+ 将本项目构建为docker镜像

  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git
  cp LDUOnlineJudge/install/docker/{Dockerfile,.dockerignore} ./
  docker build  -t lduoj:local .
  rm -rf ./{LDUOnlineJudge,Dockerfile,.dockerignore}
  ```

+ 使用构建好的镜像启动容器

  ```shell script
  docker run -dit --restart=always --cap-add=SYS_PTRACE \
        -p 8080:80 \
        -v ~/lduoj_docker:/volume \
        --name lduoj \
        lduoj:local
  ```

+ 为镜像重命名（相当于复制了一份）

  ```shell script
  docker tag lduoj:local iamwinter/lduoj:latest
  ```

+ 将镜像上传到`dockerhub`

  ```shell script
  docker login
  docker push iamwinter/lduoj:latest
  ```
