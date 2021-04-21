Ludong University Online Judge
===
  > 鲁东大学程序设计在线测评系统与考试平台

# 开发者笔记

[developer.md](developer.md)

# 快速了解

  [截屏展示](https://blog.csdn.net/winter2121/article/details/105294224)
  
  - 程序设计在线评测系统，大学生程序设计考试系统，ACM-ICPC竞赛系统
  - 支持**考试/竞赛**，支持**编程题、代码填空**（C/C++/Java/Python3）
  - Web后端基于php框架 laravel 6.0 开发，php版本=7.2
  - Web前端使用bootstrap4、jquery，适配移动端和PC端
  - 判题端基于C/C++和shell编程，存放于`judge`文件夹

  前台
  
  + 首页；公告/新闻，本周榜，上周榜
  + 状态；用户提交记录与判题结果
  + 问题；题库（编程、代码填空）
  + 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送
  + 排名；用户解题排行榜，可按年/月/周/日查询

  后台

  + 判题机；启动/停止linux判题端进程
  + 公告/新闻；用户访问首页可见
  + 用户管理；**账号权限分配**，批量生成账号，**黑名单**
  + 题目管理；增改查，公开/隐藏，重判结果，**导入与导出(兼容hustoj)**
  + 竞赛管理；增删查改，公开/隐藏
  + 系统配置；修改网站名称，打开/关闭一些全局功能，**中英文切换**等。

# 项目安装

+ **基于Linux Ubuntu 16.04 / 18.04**
[帮助:[更换软件源](https://blog.csdn.net/winter2121/article/details/103335319)]
  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge
  bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
  - 浏览器访问服务器ip进入首页  
  - **注册用户admin自动成为管理员**  
  - mysql数据库lduoj，用户lduoj@localhost(密码123456789)  
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
    [如何配置域名与端口?](https://blog.csdn.net/winter2121/article/details/107783085)  
  - `-v`参数后的`~/lduoj_docker`是用于保存数据的主机目录，可自定义。
    如需备份系统，只需将此文件夹打包备份。
  - 进入容器进行管理： `docker exec -it lduoj /bin/bash`  

# 项目升级

+ 基于Linux Ubuntu安装请执行后两行；
  基于docker安装请执行全部命令。

  ```shell script
  docker exec -it lduoj /bin/bash   # 进入docker容器
  ```
  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/lduoj_upgrade
  bash /home/lduoj_upgrade/install/ubuntu16.04/update.sh
  ```

# 项目迁移

+ 基于Ubuntu16.04 / 18.04

  1.在**原主机**备份数据库、nginx配置
  ```shell script
  bash /home/LDUOnlineJudge/install/mysql/database_backup.sh
  cp -f /etc/nginx/conf.d/lduoj.conf /home/LDUOnlineJudge/install/nginx/lduoj.conf
  ```
  2.拷贝**原主机**文件夹`/home/LDUOnlineJudge`到**新主机**相同路径。  
  3.在**新主机**上执行安装。
  ```shell script
  bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
+ 基于docker  

  1.将**原主机**文件夹`~/lduoj_docker`拷贝到**新主机**相同位置。

  2.在**新主机**基于docker安装(见[项目安装](#项目安装))。

  3.若**原主机**自定义了nginx配置文件，可自行复制到**新主机**。
    或参考[配置域名与端口](https://blog.csdn.net/winter2121/article/details/107783085)

  + 可能会遇到的问题
    - 步骤1，拷贝文件夹时，可能会遇到当前用户权限不足的情况，可直接进入容器内使用`tar`打包文件夹`/volume`，并直接使用`scp`发送到新主机。随后去新主机解压并更名为`lduoj_docker`即可。
      ```shell
      docker exec -it lduoj /bin/bash
      tar -zcvf volume.tar.gz /volume
      scp -P 22 volume.tar.gz root@104.224.179.57:~/
      ```

# 判题端使用说明

+ 启动方式
  
  A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
  B. 通过终端命令启动判题端：`bash /home/LDUOnlineJudge/judge/startup.sh`

+ 判题端配置
  
  数据库连接信息、判题线程数、判题机名称等配置项均在项目根目录下文件`.env`  
  默认判题线程数为5，可根据服务器内存及性能适当调节

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

# 开源许可

  iamwinter/LDUOnlineJudge is licensed under the 
  **[GNU General Public License v3.0](https://github.com/iamwinter/LDUOnlineJudge/blob/master/LICENSE)**  
