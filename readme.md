Ludong University Online Judge
===
> 鲁东大学程序设计在线测评系统与考试平台

# :bulb: 快速了解

**概览**

- [预览网站](http://oj.01fun.top/) ；您可以使用账号guest0(密码:9AF860CB)登录参观网站。
  该账号可以进入后台管理，不要使用该账号破坏小破站哦:smiley:
- [截屏展示](https://blog.csdn.net/winter2121/article/details/105294224)
- 程序设计在线评测系统，大学生程序设计考试系统，ACM-ICPC竞赛系统
- 支持**考试/竞赛**，支持**编程题、代码填空**（C/C++/Java/Python3）
- Web后端基于php框架 laravel 6.0 开发，php版本=7.2
- Web前端使用bootstrap4、jquery，适配移动端和PC端
- 判题端基于C/C++和shell编程，存放于`judge`文件夹

**前台**

+ 首页；公告/新闻，本周榜，上周榜
+ 状态；用户提交记录与判题结果
+ 问题；题库（编程、代码填空）
+ 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送
+ 排名；用户解题排行榜，可按年/月/周/日查询

**后台**

+ 判题机；启动/停止linux判题端进程
+ 公告/新闻；用户访问首页可见
+ 用户管理；**账号权限分配**，批量生成账号，**黑名单**
+ 题目管理；增改查，公开/隐藏，重判结果，**导入与导出(兼容hustoj)**
+ 竞赛管理；增删查改，公开/隐藏
+ 系统配置；修改网站名称，打开/关闭一些全局功能，**中英文切换**等。

开发计划

+ [x] 代码高亮。以及使用网页代码编辑器。
+ [x] 增加echarts工具进行数据分析，包括榜单、题目通过率等的图表展示。
+ [x] 增加版本号标识，以及通过网页端升级系统。
+ [ ] 增加【班级/团队】模块，可对班级布置作业；学生可在【我的作业】中查看作业。
+ [ ] 后台权限需要整顿；每个题目/竞赛，应当保存创建人，只有创建人可修改。
+ [ ] 新增竞赛类别；管理员可以自由管理竞赛的类别，含二级分类。
+ [ ] 讨论板增加审核功能，以及总开关。
+ [ ] 题号重排。目前题号在数据库作为主键，不支持题号重排。
+ [ ] 查重代码左右对比。
+ [ ] 增加`About`专栏，向用户解释判题命令等。
+ [ ] 美化UI，首页增加竞赛、新闻、照片等信息的展示。
+ [ ] 后台题面编辑增加markdown编辑器。
+ [ ] 考试模式。考试期间只允许考试账号登录，限制登录ip等。

# :wrench: 项目安装

+ **基于Linux Ubuntu 16.04 / 18.04**
  [帮助:[更换软件源](https://blog.csdn.net/winter2121/article/details/103335319)]
  ```shell script
  git clone https://gitee.com/iamwinter/LDUOnlineJudge.git
  # git clone https://github.com/iamwinter/LDUOnlineJudge.git
  bash LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
    - 浏览器访问服务器ip进入首页
    - **注册用户admin自动成为管理员**
    - mysql数据库名lduoj，默认用户lduoj@localhost(密码123456789)
    - nginx配置文件`/etc/nginx/conf.d/lduoj.conf`


+ **基于docker（推荐）**
  [帮助:[更换docker镜像源](https://blog.csdn.net/winter2121/article/details/107399812)]

  ```shell script
  docker run -dit --restart=always --cap-add=SYS_PTRACE \
      -p 8080:80 \
      -v ~/lduoj_docker:/volume \
      --name lduoj \
      iamwinter/lduoj:latest
  ```

    - `-p`参数后的`8080`是主机端口，可自定义。
      浏览器访问`服务器ip:8080`进入首页。
      [配置域名与端口](https://blog.csdn.net/winter2121/article/details/107783085)
    - `-v`参数后的`~/lduoj_docker`是用于保存数据的主机目录，可自定义。
      如需备份系统，只需将此文件夹打包备份。
    - 进入容器进行管理： `docker exec -it lduoj /bin/bash`

# :hammer: 项目升级

+ 基于Linux Ubuntu安装的用户请执行后两行；
  基于docker安装的用户请执行全部命令。

  ```shell script
  docker exec -it lduoj /bin/bash
  ```
  ```shell script
  git clone https://gitee.com/iamwinter/LDUOnlineJudge.git /home/lduoj_upgrade
  # git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/lduoj_upgrade
  bash /home/lduoj_upgrade/install/ubuntu16.04/update.sh /home/LDUOnlineJudge
  ```
  注：最后一条命令最后的参数是项目安装路径，请填写您的安装路径。

# :cd: 项目迁移

+ 基于Ubuntu16.04 / 18.04（以安装路径`/home/LDUOnlineJudge`为例）

  1.在**原主机**备份数据库
  ```shell script
  bash /home/LDUOnlineJudge/install/mysql/database_backup.sh
  ```
  2.拷贝**原主机**文件夹`/home/LDUOnlineJudge`到**新主机**相同路径。  
  3.在**新主机**上执行安装。
  ```shell script
  bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
+ 基于docker

  1.在**原主机**将文件夹`~/lduoj_docker`（或docker容器内`/volume`）打包，发送到**新主机**相同位置。

    - 原主机 [ 进入容器 -> 打包压缩 -> 发送到新主机(用户名`root`；ssh端口号`22`；实际ip`ip`) ]：
  ```shell
  docker exec -it lduoj /bin/bash
  tar -zcvf volume.tar.gz /volume
  scp -P 22 volume.tar.gz root@ip:~/
  ```
    - 新主机 [ 解压 -> 重命名 ]：
  ```shell
  cd ~/
  tar -zxvf volume.tar.gz
  mv volume lduoj_docker
  ```

  2.在**新主机**基于docker安装(见[项目安装](#项目安装))。

# :mega: 判题端使用说明

+ 启动方式

  A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
  B. 通过终端命令启动判题端：`bash /home/LDUOnlineJudge/judge/startup.sh`

+ 判题端配置

  详见`.env`：
  ```shell
  #判题端配置
  JG_MAX_RUNNING=1  # 并行判题进程数
  JG_DATA_DIR=/home/LDUOnlineJudge/storage/app/data  # 测试数据所在目录
  JG_NAME="Master"  # 判题机名称
  ```
  其中，`JG_MAX_RUNNING`默认值为1，请在安装后自行修改`.env`，参考值：

<div align="center">

| 服务器核心数 | 服务器内存 | `JG_MAX_RUNNING`建议值 |
| --- | --- | --- |
| ≤2 | ≤1GB | 1 |
| ≤4 | ≤4GB | 2 |
| ≤8 | ≤8GB | 4 |
| ≤16 | ≤16GB | 8 |
| \>16 | \>16GB | ≥8 |

</div>
注：修改`.env`后，执行`php artisan optimize`生效。

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
  然后开启子进程(`judge/cpp/judge.cpp`)判题（最多并行5个）。
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
          iamwinter/lduoj:latest
    ```
   `-p`指定了8036端口作为宿主机mysql端口，8080端口作为网页入口。  
   其中`-v`指定了项目映射到`D:\myproject\LDUOnlineJudge`，在本地编辑该项目即可。

2. 远程连接mysql

    ```shell
    # 进入docker容器内
    docker exec -it lduoj /bin/bash
   
    # 修改mysql配置，允许任意主机访问
    sed -i 's/^bind-address.*$/bind-address=0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
    service mysql restart
    # 新建允许外部登录的mysql用户：'lduoj'@'%'，密码123456789
    USER=`cat /etc/mysql/debian.cnf |grep user|head -1|awk '{print $3}'`
    PW=`cat /etc/mysql/debian.cnf |grep password|head -1|awk '{print $3}'`
    mysql -u${USER} -p${PW} -e"CREATE USER If Not Exists 'lduoj'@'%' IDENTIFIED WITH mysql_native_password BY '123456789';"
    mysql -u${USER} -p${PW} -e"GRANT all privileges ON lduoj.* TO 'lduoj'@'%' identified by '123456789';flush privileges;"
    ```
    然后远程连接【**宿主机ip**:8036】，使用上一步新建的用户lduoj登录mysql即可。

3. 修改远程关联仓库。前提：先fork本项目到你自己的github账户下。
    ```shell
    cd LDUOnlineJudge
    git remote rm origin
    git remote add origin https://github.com/Your_GitHub_Username/LDUOnlineJudge.git
    git remote -v
    git fetch --all  # 从远程拉取最新的代码 不merge
    git reset --hard origin/master  # 使用指定分支的代码（此处master）强制覆盖本地代码
    ```

方式二：基于本地环境
1. 下载源码
    ```shell script
    git clone https://gitee.com/iamwinter/LDUOnlineJudge.git
    # git clone https://github.com/iamwinter/LDUOnlineJudge.git
    cd LUDOnlineJudge
    ```

2. 准备环境

    + 推荐`phpstudy`（含php、mysql、nginx等环境）。推荐IDE是`PhpStorm`。

    + 数据库创建：`LDUOnlineJudge/install/mysql/lduoj.sql`。

    + nginx配置：`LDUOnlineJudge/install/nginx/lduoj.conf`。
      也可以自行配置apache服务器。

3. 填写配置文件

    ```shell script
    cp -rf .env.example .env
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


# :earth_asia: Docker镜像发布

+ 将本项目构建为docker镜像，**务必**在一个新建文件夹内操作（如`./lduoj_build`，结束后删除即可）

  ```shell script
  mkdir lduoj_build && cd lduoj_build
  git clone https://gitee.com/iamwinter/LDUOnlineJudge.git
  # git clone https://github.com/iamwinter/LDUOnlineJudge.git
  docker build -f ./LDUOnlineJudge/install/docker/Dockerfile -t lduoj:local .
  ```

+ 为镜像重命名（相当于复制了一份，请将用户名`iamwinter`替换）

  ```shell script
  docker tag lduoj:local iamwinter/lduoj:latest
  ```

+ 将镜像上传到`dockerhub`

  ```shell script
  docker login
  docker push iamwinter/lduoj:latest
  ```

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
