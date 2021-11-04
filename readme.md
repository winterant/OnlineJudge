Ludong University Online Judge
===
> 鲁东大学程序设计在线测评系统与考试平台

# :bulb: 快速了解

程序设计在线评测系统(`Online Judge`)常见于程序设计竞赛、ACM集训、算法考试和教学任务中。
`LDUOnlineJudge`可分为两个部分：1.基于laravel 6.0（PHP7.2）开发的web端；
2.基于C语言开发的判题端（源码位于`./judge/`）。
Web端可供学生查阅题目、参加比赛/考试、提交代码等，供管理员管理后台、编写题目、组织竞赛/考试等。
判题端可将学生提交的代码进行评判，基于C语言实现在Linux系统下对选手代码进行评测。
判题端使用Ptrace监视选手子进程，严格限制时间、空间，严格禁止系统调用；
同时支持出题人自行编写评判程序对选手程序运行结果进行评判。
欢迎访问LDUOJ([http://lduoj.top](http://lduoj.top))。
您还可以查看[截屏展示](https://blog.csdn.net/winter2121/article/details/105294224) 。

**前台**

+ 首页；公告/新闻，本周榜，上周榜。
+ 状态；用户提交记录与判题结果。
+ 问题；题库（支持编程题、代码填空题）。
+ 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送。
+ 排名；用户解题排行榜，可按年/月/周/日查询。

**后台**

+ 判题进程；启动/停止linux判题端进程。
+ 公告新闻；用户访问首页可见。
+ 用户管理；**账号权限分配**，批量生成账号，**黑名单**。
+ 题目管理；增改查，公开/隐藏，重判结果，**导入与导出(兼容hustoj)**。
+ 竞赛管理；增删查改，公开/隐藏。
+ 系统配置；修改网站名称，打开/关闭一些全局功能，**中英文切换**，系统在线升级等。

下文拉取代码的git命令如果速度缓慢，可以尝试使用镜像站地址，例如
```bash
git clone https://github.com.cnpmjs.org/iamwinter/LDUOnlineJudge.git
```

# :wrench: 项目安装

+ **基于Linux Ubuntu 18.04/20.04**
  [帮助:[更换中科大软件源](https://mirrors.ustc.edu.cn/help/ubuntu.html#id7)]
  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git
  bash LDUOnlineJudge/install/ubuntu/install.sh
  ```
  - 浏览器访问服务器ip进入首页。
  - **注册用户admin自动成为管理员**。
  - mysql数据库名lduoj，默认用户lduoj@localhost(密码123456789)。
  - nginx配置文件`/etc/nginx/conf.d/lduoj.conf`


+ **基于docker（推荐）**
  [帮助:[更换docker镜像源](https://blog.csdn.net/winter2121/article/details/107399812)]

  ```shell script
  docker run -dit --restart=always --cap-add=SYS_PTRACE \
      -p 8080:80 \
      -v ~/lduoj_docker:/volume \
      --name lduoj \
      iamwinter/lduoj
  ```

  - `-p`指定`8080`作为web端口，
    浏览器访问`服务器ip:8080`进入首页。
    [帮助:[配置域名与端口](https://blog.csdn.net/winter2121/article/details/107783085)]
  - `-v`指定`~/lduoj_docker`作为保存项目的宿主机目录。
  - 进入容器进行管理： `docker exec -it lduoj /bin/bash`。

# :hammer: 项目升级

+ 基于docker安装的用户请先进入容器（`docker exec -it lduoj /bin/bash`）。

  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git oj_upgrade
  bash oj_upgrade/install/ubuntu/update.sh /home/LDUOnlineJudge  # 最后这个参数为原项目路径
  ```

# :cd: 项目迁移（备份）

+ 基于Ubuntu

  1.在**原主机**备份数据库
  ```shell script
  bash install/mysql/database_backup.sh
  ```
  2.拷贝**原主机**项目文件夹（即`LDUOnlineJudge/`）到**新主机**。  
  3.在**新主机**上执行安装。
  ```shell script
  bash install/ubuntu/install.sh
  ```

+ 基于docker

  1.在**原主机**将文件夹`~/lduoj_docker`（或docker容器内`/volume`）打包，发送到**新主机**
  ```shell
  tar -zcvf volume.tar.gz /volume     # 打包
  scp -P 22 volume.tar.gz root@ip:~/  # 发送到新主机`~/`下；也可以自行拷贝
  ```
  2.在新主机解压收到的压缩文件
  ```shell
  tar -zxvf volume.tar.gz   # 解压
  ```
  3.在新主机[基于docker安装](#项目安装)，**参数`-v`挂载步骤2解压出的目录(绝对路径)**。

# :mega: 判题端使用说明

+ 启动方式

  A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
  B. 通过终端命令启动判题端：`bash judge/startup.sh`

+ 判题端配置
  ```shell
  JG_DATA_DIR=storage/app/data  # 测试数据所在目录，**请勿修改!**
  JG_NAME="Master"              # 判题机名称，可修改
  JG_MAX_RUNNING=1              # 并行判题进程数，建议值为可用内存(GB)/2
  ```
  注：以上配置在文件`.env`或`judge/config.js`中均可配置。
  判题端默认使用`.env`中的配置（执行`php artisan optimize`生效）。
  若需单独配置判题端，可在`judge/config.js`中配置。

# :page_facing_up: 整体架构

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

  Web前端使用bootstrap4、jquery，适配移动端和PC端。

  网页端向用户展示题目等相关信息，用户可通过题目或竞赛途径上传自己的代码。

  判题机启动时，轮询程序(`judge/cpp/polling.cpp`)将不停查询数据库，
  收集未判的提交记录（提交编号），
  然后开启子进程(`judge/cpp/judge.cpp`)判题（并行）。
  对于每一个判题进程，主要步骤是：从数据库读取代码、编译、输入数据运行、
  输出结果与正确结果对比（或者特判）、将判题结论写入数据库。

# :computer: 本地二次开发

方式一：基于docker

1. 启动容器
    ```shell
    docker run -dit --restart=always --cap-add=SYS_PTRACE \
          -p 8080:80 \
          -p 8036:3306 \
          -v /d/myproject:/volume \
          --name lduoj \
          iamwinter/lduoj
    ```
  - `-p`指定8036端口作为宿主机mysql端口，指定8080端口作为网页入口。
  - `-v`将数据映射到本地`D:\myproject\{LDUOnlineJudge, mysql}`，本地编辑项目即可。
  - 浏览器访问`http://localhost:8080`显示主页则表示部署成功。

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

方式二：基于本地环境
1. 下载源码
    ```shell script
    git clone https://github.com/iamwinter/LDUOnlineJudge.git
    ```

2. 准备环境

  + PHP >=7.2 （必需拓展：php7.2-fpm php7.2-mysql php7.2-xml php7.2-mbstring）
  + mysql >=5.7 （建库脚本：`install/mysql/lduoj.sql`）
  + 判题环境需求（只能在linux系统运行）：
    g++ libmysqlclient-dev openjdk-8-jre openjdk-8-jdk python3.6 make flex

3. 填写配置文件

    ```shell script
    cp -rf .env.example .env
    ```
   然后编辑`.env`文件，填写mysql连接信息。

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
    浏览器访问`http://localhost:8000`显示主页则表示部署成功。
    生产环境中请配置nginx。

# :earth_asia: Docker镜像发布

+ 将本项目构建为docker镜像，**务必**在一个新建文件夹内操作（如`./lduoj_build`，结束后删除即可）

  ```shell
  git config --global core.autocrlf input  # 仅windows用户执行
  ```

  ```shell script
  mkdir lduoj_build && cd lduoj_build
  git clone https://github.com/iamwinter/LDUOnlineJudge.git
  docker build -f ./LDUOnlineJudge/install/docker/Dockerfile -t lduoj:local .
  ```
  windows用户git默认`autocrlf=true`，
  执行`git clone`下载的文件换行符会被自动转换为`\r\n`，
  所以下载前需要手动修改配置`autocrlf=input`。

+ 为镜像重命名（相当于复制了一份，请将用户名`iamwinter`替换）

  ```shell script
  docker tag lduoj:local iamwinter/lduoj
  ```

+ 将镜像上传到`dockerhub`

  ```shell script
  docker login
  docker push iamwinter/lduoj
  ```

# :memo: 开发日志

| 提出日期 | 开发计划 | 备注 | 完成日期 | 开发者 |
|---|---|---|---|---|
|2021.09.03|客户端登录加密| | | |
|2021.08.21|判题潜在bug：系统调用没有完全禁用，如可以提交python代码攻击服务器。解决方案可以使用chroot()| | | |
|2021.08.17|需求：竞赛题目，右侧显示题目列表| | | |
|2021.08.17|Web：Admin，将设置移动到各自的模块去。其中滚动公告改为指定公告| | | |
|2021.08.16|Web：榜单和气球页面可以设置气球图标（含颜色）| | | |
|2021.08.16|Web：后台大部分table对左右滑动适配把标题也移动了，另外“操作”按钮换行了。（黑名单输入框对手机太宽）|已全部调整|2021.08.19|iamwinter|
|2021.06.25|将中英文切换功能放到主页导航栏，用户自由切换。|cookie记住用户选择|2021.06.26|[iamwinter](https://github.com/iamwinter)|
|2021.06.23|后台权限需要整顿；每个题目/竞赛，应当保存创建人，只有创建人可修改。| |2021.06.30|iamwinter|
|2021.06.23|增加【班级/团队】模块，可对班级布置作业；学生可在【我的作业】中查看作业。| | | |
|2021.06.23|新增竞赛类别；管理员可以自由管理竞赛的类别，含二级分类。分出一栏“我的进行中”|除“进行中”外完成 |2021.11.04|iamwinter|
|2021.06.23|讨论板增加审核功能，总开关：权限分配。前端js动态生成语句凌乱，需重构| | | |
|2021.05.01|增加echarts工具进行数据分析，包括榜单、题目通过率等的图表展示。| 仅problem页面；可在其他页面继续增加 |2021.05.11|[iamwinter](https://github.com/iamwinter)|
|2021.05.01|代码高亮。以及使用网页代码编辑器。| |2021.05.11|[iamwinter](https://github.com/iamwinter)|
|2021.03.30|美化UI，首页增加竞赛、新闻、照片等信息的展示。| | | |
|2021.03.01|查重代码左右对比。| | | |
|2021.03.01|增加`About`专栏，向用户解释判题命令、使用手册等。滚动公告可自行设置id，公告直接作为`About`即可| | | |
|2021.01.01|考试模式。考试期间只允许考试账号登录，限制登录ip等。| | | |

# :bug: bug修复

| 发现日期 | 具体描述 | 备注 | 修复日期 | 开发者 |
|---|---|---|---|---|
|2021.06.24|安装脚本`install.sh`中`sed`命令后面的变量需要转移斜杠`/`| |2021.06.24|[iamwinter](https://github.com/iamwinter)|

# :gift_heart: 鸣谢

[zhblue/hustoj](https://github.com/zhblue/hustoj)  
[sim](https://dickgrune.com/Programs/similarity_tester/)  
[laravel-6.0](https://laravel.com/)  
[bootstrap-material-design](https://fezvrasta.github.io/bootstrap-material-design/)  
[jquery-3.4.1](https://jquery.com/)  
[font-awesome](http://www.fontawesome.com.cn/)  
[ckeditor-5](https://ckeditor.com/ckeditor-5/)  
[MathJax](https://www.mathjax.org/)  
[zhiyul/switch](https://github.com/notiflix/Notiflix)  
[codemirror](https://codemirror.net/)  
[highlight.js](https://highlightjs.org/)

# :scroll: 开源许可

iamwinter/LDUOnlineJudge is licensed under the
**[GNU General Public License v3.0](https://github.com/iamwinter/LDUOnlineJudge/blob/master/LICENSE)**  
