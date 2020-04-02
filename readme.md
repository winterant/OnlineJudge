Ludong University Online Judge
===
  > 鲁东大学程序设计在线测评系统与教学平台（开发中）


# 概述

  - 程序设计在线评测系统，大学生程序设计考试系统，ACM-ICPC竞赛系统
  - 基于php框架 laravel 6.0 开发，php版本=7.2
  - web端与判题端分离，judge文件夹为判题端程序，独立于laravel
  
  前台概览
  
  + 首页；公告/新闻，本周榜，上周榜
  + 状态；用户提交记录与判题结果
  + 问题；题库
  + 竞赛；题目(选择,填空,编程)，排名(ACM,OI,考试)，**赛后补题榜**，公告栏，气球派送；
  + 排名；用户解题排行榜，可按年/月/周/日查询
  
  后台功能概览

  + 判题机；启动/停止linux判题端进程
  + 公告/新闻；用户访问首页可见
  + 用户管理；权限授权，批量生成账号
  + 题目管理；增改查，公开/隐藏，重判结果，导入与导出(兼容hustoj)
  + 竞赛管理；增删查改，公开/隐藏
  + 系统配置；修改网站名称，打开/关闭一些全局功能

# 项目安装
  
  1. **Linux Ubuntu 16.04**
   
  在终端执行以下命令，安装过程约10分钟。若下载缓慢请先[更换软件源](https://blog.csdn.net/winter2121/article/details/103335319)再重新安装。
  ```
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge
  bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
  **注意**：安装过程mysql**可能**会提示设置root密码，请输入并谨记。
  
  **[安装成功]**：
  浏览器访问服务器ip即可打开首页(云服务器请先在控制台安全组添加80端口)；
  注册用户admin自动成为管理员
  
  **[运维须知]**： 
  
  1.安装后项目位于/home/LDUOnlieJudge
  
  2.自动安装mysql5.7，管理员root@localhost(密码rootroot)，
  该项目专用用户lduoj@localhost(密码123456789)。
  
  **为保证安全性请及时修改数据库密码**
  (注：需同时修改①ubuntu下mysql用户，②项目下.env文件数据库配置，③项目根目录下执行`php artisan config:cache`)
  
  3.配置域名及端口：在/etc/nginx/conf.d/lduoj.conf文件内，在`server_name`后面填域名。

# 项目备份
  ```
  bash /home/LDUOnlineJudge/install/ubuntu16.04/backup.sh
  ```
  **提示**：产生备份`/home/lduoj_backup/lduoj_{日期}`，
  包含项目文件夹（含测试数据、图片、文件）、数据库备份lduoj.sql、nginx配置文件lduoj.conf

# 项目恢复
  从备份中恢复整个系统；执行前请替换命令中的中文
  ```
  bash /home/LDUOnlineJudge/install/ubuntu16.04/recover.sh  /home/lduoj_backup/备份名
  ```

# 项目升级

  建议升级前进行一次备份，若升级出错，可恢复系统。
  ```
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/lduoj_temp
  cp /home/lduoj_temp/install/ubuntu16.04/update.sh /home/
  bash /home/update.sh
  ```
# 项目迁移（更换服务器）
  1. 在原服务器执行一次**项目备份**，并将备份好的文件夹拷贝到新服务器相同文件夹！  
  2. 在新服务器执行一次**项目安装**  
  3. 在新服务器执行一次**项目恢复**  
  
  **注**：若新服务器已安装mysql，需要自行新建数据库lduoj，
  并使用备份文件lduoj.sql恢复数据。还要修改项目下.env文件的mysql用户信息

# 判题端使用说明
  
  + 启动方式
  
    A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
    B. 通过终端命令启动判题端：bash /home/LDUOnlineJudge/judge/startup.sh

  + 判题端配置
  
    数据库连接信息、最大判题数、判题机名称等配置项均在项目根目录下.env文件
  
  + 如何多服务器判题？
  
   1. 首先在一台服务器上部署好本项目，称其为主服务器；主服务器将唯一承担mysql数据库、网页  
   2. 授权mysql允许远程访问：  
      登录mysql控制台，并执行命令（请替换中文提示）：
      ```
      GRANT ALL PRIVILEGES ON lduoj.* TO '主服务器mysql用户名'@'%' IDENTIFIED BY '密码' WITH GRANT OPTION;
      FLUSH PRIVILEGES;
      exit;
      ```
   3. 在其他服务器称为从服务器，可以有多台，只负责判题;  
      故只需要从主服务器克隆judge/、storage/app/data/、.env  
      克隆后请保持位置与主服务器一致(即.env仍位于/home/LDUOnlineJudge/.env)  
   4. 编辑.env，将其中数据库连接信息修改为主服务器ip和第2步授权的用户  
   5. 在从服务器上安装判题所需的环境支持：`bash /home/LDUOnlineJudge/judge/install.sh`  
   6. 在从服务器上启动判题端：`bash /home/LDUOnlineJudge/judge/startup.sh`  
   7. 在从服务器上停止判题端：`bash /home/LDUOnlineJudge/judge/stop.sh`


# 展示
