Ludong University Online Judge
===
  > 鲁东大学程序设计在线测评系统与考试平台


# 快速了解

  项目演示：http://test.winterstar.cn  
  截屏展示：https://blog.csdn.net/winter2121/article/details/105294224
  
  - 程序设计在线评测系统，大学生程序设计考试系统，ACM-ICPC竞赛系统
  - 支持考试/竞赛，支持选择题、填空题（含代码填空）、编程题（C/C++/Java/Python3）
  - Web后端基于php框架 laravel 6.0 开发，php版本=7.2
  - Web前端使用bootstrap4、jquery，适配移动端和PC端
  - 判题端基于C/C++和shell编程，存放于judge文件夹
  
  前台
  
  + 首页；公告/新闻，本周榜，上周榜
  + 状态；用户提交记录与判题结果
  + 问题；题库（编程、代码填空）
  + 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送
  + 排名；用户解题排行榜，可按年/月/周/日查询
  
  后台

  + 判题机；启动/停止linux判题端进程
  + 公告/新闻；用户访问首页可见
  + 用户管理；权限授权，批量生成账号，黑名单
  + 题目管理；增改查，公开/隐藏，重判结果，导入与导出(兼容hustoj)
  + 竞赛管理；增删查改，公开/隐藏
  + 系统配置；修改网站名称，打开/关闭一些全局功能，中英文切换等。

# 项目安装

+ **基于Linux Ubuntu 16.04**
  
    若下载缓慢请先[更换软件源](https://blog.csdn.net/winter2121/article/details/103335319)再重新安装。
    ```
    git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge
    bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
     ```
    - 浏览器访问服务器ip进入首页  
    - 安装后项目位于/home/LDUOnlieJudge，请勿移动  
    - **注册用户admin自动成为管理员**
    - mysql数据库lduoj，用户lduoj@localhost(密码123456789)  
      (配置文件:`.env`，配置生效:`php /home/LDUOnlineJudge/artisan config:cache`)  
    - 域名&端口：配置文件`/etc/nginx/conf.d/lduoj.conf`，配置生效:`service nginx restart`  


+ **基于docker**

    若docker build缓慢，请先[更换docker镜像源](https://blog.csdn.net/winter2121/article/details/107399812)
    ```
    git clone https://github.com/iamwinter/LDUOnlineJudge.git
    docker build  -t lduoj -f ./LDUOnlineJudge/install/docker/Dockerfile .
    docker run -d --restart=always --cap-add=SYS_PTRACE -p 8080:80 -v ~/lduoj_docker:/volume --name lduoj lduoj:latest
    ```
    - 浏览器访问服务器ip:8080进入首页。[如何配置域名与端口?](https://blog.csdn.net/winter2121/article/details/107783085)  
    - 进入容器进行管理： `dockder exec -it 容器id /bin/bash`  

# 项目备份

  产生系统备份`/home/lduoj_backup/lduoj_{日期}`。
  - Ubuntu16.04 或 docker容器内
    ```
    bash /home/LDUOnlineJudge/install/ubuntu16.04/backup.sh
    ```
  
# 项目恢复
  
  从已有备份中恢复系统。
  - Ubuntu16.04 或 docker容器内
    ```
    bash /home/LDUOnlineJudge/install/ubuntu16.04/recover.sh  /home/lduoj_backup/备份名
    ```

# 项目升级

  建议升级前进行一次备份，若升级失败，可恢复系统。
  - Ubuntu16.04 或 docker容器内
    ```
    git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/lduoj_upgrade
    cp /home/lduoj_upgrade/install/ubuntu16.04/update.sh /home/
    bash /home/update.sh
    ```

# 判题端使用说明

  + 启动方式
  
    A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
    B. 通过终端命令启动判题端：`bash /home/LDUOnlineJudge/judge/startup.sh`

  + 判题端配置
  
    数据库连接信息、判题线程数、判题机名称等配置项均在项目根目录下.env文件  
    默认判题线程数为5，可根据服务器内存及性能适当调节

# 本地开发

  ```
  git clone https://github.com/iamwinter/LDUOnlineJudge.git
  cd LDUOnlineJudge
  cp .env.example .env
  composer install
  php artisan storage:link
  php artisan key:generate
  php artisan optimize
  ```
  - `LDUOnlineJudge/install/mysql/lduoj.sql`创建数据库，`.env`配置mysql  
  - 若使用nginx，配置文件中需添加：
    ```shell script
    location / {
      try_files $uri $uri/ /index.php?$query_string;
    }
    ```

# 鸣谢

  [zhblue/hustoj](https://github.com/zhblue/hustoj)  
  [sim](https://dickgrune.com/Programs/similarity_tester/)  
  [laravel-6.0](https://laravel.com/)  
  [bootstrap-material-design](https://fezvrasta.github.io/bootstrap-material-design/)  
  [jquery-3.4.1](https://jquery.com/)  
  [font-awesome](http://www.fontawesome.com.cn/)  
  [ckeditor-5](https://ckeditor.com/ckeditor-5/)  
  [MathJax](https://www.mathjax.org/)  
  [zhiyul/switch](https://github.com/notiflix/Notiflix)  
  [wow.js](https://www.delac.io/wow/)

# 版本信息

  iamwinter/LDUOnlineJudge is licensed under the 
  **[GNU General Public License v3.0](https://github.com/iamwinter/LDUOnlineJudge/blob/master/LICENSE)**
