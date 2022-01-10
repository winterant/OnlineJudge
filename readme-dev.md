<h1 align="center">开发者文档</h1>

# 💻 本地开发

## 方式一：基于docker

1. 启动容器

    ```shell
    docker run -d -p 8080:80 -p 8036:3306 \
          -v /d/myproject/volume:/volume \
          -v /d/myproject/LDUOnlineJudge:/home/LDUOnlineJudge \
          --restart always \
          --name lduoj \
          winterant/lduoj
    ```

+ `-p`指定8036端口作为宿主机mysql端口，指定8080端口作为网页入口。
+ `-v`将数据映射到本地`D:/myproject/LDUOnlineJudge`，本地编辑项目即可。
+ 浏览器访问`http://localhost:8080`显示主页则表示部署成功。

2. 连接docker内的mysql数据库（非必需）（等同于远程连接mysql）

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

1. 下载源码

    ```shell script
    git clone https://github.com/winterant/LDUOnlineJudge.git
    ```

2. 准备环境

+ PHP >=7.2 （必需拓展：php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring）
+ mysql >=5.7 （建库脚本：`install/mysql/lduoj.sql`）
+ 判题环境需求（只能在linux系统运行）：
    g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex

3. 配置文件

    将文件`.env.example`复制一份名为`.env`，可编辑其中的相关配置。

4. 初始化项目

    ```
    chown www-data:www-data -R storage bootstrap/cache  # linux系统需要赋权
    composer install --ignore-platform-reqs             # 下载laravel依赖
    
    mkdir -p storage/app/public # 新建前端静态文件存储目录
    php artisan storage:link    # 将静态文件存储目录软连接到public/storage
    php artisan key:generate    # 必需，生成.env中的APP_KEY
    php artisan optimize        # 非必需，优化汇总所有配置；开发阶段可不执行
    ```

5. 启动服务，预览主页。

    ```shell
    php -S 127.0.0.1:8000  # 或 php artisan serve --port=8000
    ```

    浏览器访问`http://localhost:8000`显示主页则表示环境搭建成功。

# 🌏 Docker镜像发布

+ 将本项目构建为docker镜像
  
  ```bash
  git clone https://github.com/winterant/LDUOnlineJudge.git
  cd LDUOnlineJudge
  docker build -f install/docker/Dockerfile -t lduoj:local .
  ```
  注意：Windows用户请从网页下载源码，若使用`git clone`则会自动将所有文件行末`\n`自动转换为`\r\n`。若坚持使用`git clone`获取源码，请在获取前修改`git`配置
  ```bash
  git config --global core.autocrlf input
  ```

+ 为镜像重命名

  ```bash
  docker tag lduoj:local winterant/lduoj
  ```

+ 将镜像发布到`dockerhub`

  ```bash
  docker push winterant/lduoj
  ```

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

# 📝 开发日志

| 提出日期 | 开发计划 | 备注 | 完成日期 | 开发者 |
|---|---|---|---|---|
|2021.12.30|使用docker-compose启动容器；使用judge0作为判题服务| | | |
|2021.12.10|权限管理列表增加一键批量删除| | | |
|2021.09.03|客户端登录加密| | | |
|2021.08.21|判题潜在bug：系统调用没有完全禁用，如可以提交python代码攻击服务器。解决方案可以使用chroot()| | | |
|2021.08.17|需求：竞赛题目，右侧显示题目列表| | | |
|2021.08.17|Web：Admin，将设置移动到各自的模块去。其中滚动公告改为指定公告| | | |
|2021.08.16|Web：榜单和气球页面可以设置气球图标（含颜色）| | | |
|2021.08.16|Web：后台大部分table对左右滑动适配把标题也移动了，另外“操作”按钮换行了。（黑名单输入框对手机太宽）|已全部调整|2021.08.19|winterant|
|2021.06.25|将中英文切换功能放到主页导航栏，用户自由切换。|cookie记住用户选择|2021.06.26|[winterant](https://github.com/winterant)|
|2021.06.23|后台权限需要整顿；每个题目/竞赛，应当保存创建人，只有创建人可修改。| |2021.06.30|winterant|
|2021.06.23|增加【班级/团队】模块，可对班级布置作业；学生可在【我的作业】中查看作业。| | | |
|2021.06.23|新增竞赛类别；管理员可以自由管理竞赛的类别，含二级分类。分出一栏“我的进行中”|除“进行中”外完成 |2021.11.04|winterant|
|2021.06.23|讨论板增加审核功能，总开关：权限分配。前端js动态生成语句凌乱，需重构| | | |
|2021.05.01|增加echarts工具进行数据分析，包括榜单、题目通过率等的图表展示。| 仅problem页面；可在其他页面继续增加 |2021.05.11|[winterant](https://github.com/winterant)|
|2021.05.01|代码高亮。以及使用网页代码编辑器。| |2021.05.11|[winterant](https://github.com/winterant)|
|2021.03.30|美化UI，首页增加竞赛、新闻、照片等信息的展示。| | | |
|2021.03.01|查重代码左右对比。| | | |
|2021.03.01|增加`About`专栏，向用户解释判题命令、使用手册等。滚动公告可自行设置id，公告直接作为`About`即可| | | |
|2021.01.01|考试模式。考试期间只允许考试账号登录，限制登录ip等。| | | |
