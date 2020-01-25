Ludong University Online Judge
===
  > 鲁东大学程序设计在线测评系统与教学平台 

# 警告：系统处于开发阶段，以下内容可能有错误之处！

# 概述

  - 基于php框架 laravel 6.0 开发，php版本>=7.2
  - web端与判题端分离，judge文件夹为判题端程序，独立于laravel

# 一键安装

  **前提**：使用新装纯净系统，未安装：php7.2, php7.2-fpm, composer, nginx, mysql
  
  **注意**：项目自动部署到/home/下
  1. Linux Ubuntu 16.04
    
  ```
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/install.sh
  chmod +x install.sh
  ./install.sh
  ```
  
  **成功**：打开浏览器输入地址你的服务器ip地址即可访问首页（默认占用80端口）
  
### + 答疑
    
  1.提示`wget`不存在？请先安装：`apt-get update && apt install -y wget`
  
  2.为什么安装时速度很慢？可能是由于你的系统镜像源在国外，下载资源太慢。修改为国内镜像源：
  ```
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/alter_sources.sh
  chmod +x alter_sources.sh
  ./alter_sources.sh
  ```
  
  3.访问ip打不开？①若为云服务器请登录控制台检查安全组是否开放对应端口。
   ②仍失败，请删除nginx配置示例`rm -rf /etc/nginx/sites-available/default`
   **或**把80端口改为其他端口(如8001)`vim /etc/nginx/conf.d/lduoj.conf`。
  
  4.如何配置域名？`vim /etc/nginx/conf.d/lduoj.conf`，在server_name后面填域名，不要带前缀http://
  
  5.如何手动部署？请阅读安装脚本
   <a href="https://github.com/iamwinter/LDUOnlineJudge/blob/master/install/ubuntu16.04/install.sh" target="_blank">install.sh</a>
   ,根据实际情况执行所需命令。
   
### + 系统更新
  ```
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/update.sh
  chmod +x update.sh
  ./update.sh
  ```
  **提示**：已自动备份整个项目目录为`lduoj_last_version/`


# 展示

