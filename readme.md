Ludong University Online Judge
===
  > 鲁东大学程序设计在线测评系统与教学平台 



# 警告：系统处于开发阶段，以下内容可能有错误之处！

# 概述

  - 基于php框架 laravel 6.0 开发，php版本>=7.2
  - web端与判题端分离，judge文件夹为判题端程序，独立于laravel

# 一键安装
  
  1. **Linux Ubuntu 16.04**
   
  终端执行以下命令，安装过程10~30分钟。若耗时过长请先<a href="https://blog.csdn.net/winter2121/article/details/103335319" target="_blank">更换软件源</a>再重新安装。
  ```
  apt update && apt -y install wget
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/install.sh
  bash install.sh
  ```
  
  **[安装成功]**：浏览器访问你的服务器ip即可打开首页(nginx默认占用80端口,云服务器请先在控制台安全组添加该端口)
  
  **[运维须知]**： 
  
  1.安装后项目位于/home/LDUOnlieJudge
  
  2.自动安装mysql5.7，管理员root@localhost(密码rootroot)，
  该项目专用用户lduoj@localhost(密码123456789)。
  
  **为保证安全性请及时修改**
  (注：需同时修改①ubuntu下mysql，②项目下.env文件数据库配置，③项目下judge/cpp/下的数据库配置，
  ④最后在项目根目录下执行`php artisan config:clear && php artisan config:cache`)
  
  3.配置域名：在/etc/nginx/conf.d/lduoj.conf文件内，在`server_name`后面填域名。


# 系统更新
  ```
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/update.sh
  bash update.sh
  ```
  或
  ```
  bash /home/LDUOnlineJudge/intall/ubuntu16.04/update.sh
  ```
  **提示**：更新时自动产生备份`/home/lduoj_update/ldu_{日期}`，
  包含项目文件夹、数据库备份lduoj.sql、nginx配置文件lduoj.conf

# 系统备份
  ```
  wget https://raw.githubusercontent.com/iamwinter/LDUOnlineJudge/master/install/ubuntu16.04/backup.sh
  bash backup.sh
  ```
  或
  ```
  bash /home/LDUOnlineJudge/intall/ubuntu16.04/backup.sh
  ```
  **提示**：产生备份`/home/lduoj_update/ldu_{日期}`，
  包含项目文件夹、数据库备份lduoj.sql、nginx配置文件lduoj.conf


# 展示

