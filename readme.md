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
  + 竞赛；竞赛列表
  + 排名；用户解题排行榜，可按年/月/周/日查询
  
  后台功能概览

  + 公告/新闻；用户访问首页可见
  + 用户管理；权限授权，批量生成账号
  + 题目管理；增改查，公开/隐藏，重判结果，导入与导出(兼容hustoj)
  + 竞赛管理；增删查改，公开/隐藏

# 安装
  
  1. **Linux Ubuntu 16.04**
   
  在终端执行以下命令，安装过程约10分钟。若下载缓慢请先[更换软件源](https://blog.csdn.net/winter2121/article/details/103335319)再重新安装。
  ```
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/LDUOnlineJudge
  bash /home/LDUOnlineJudge/install/ubuntu16.04/install.sh
  ```
  **注意**：安装过程mysql**可能**会提示设置root密码，请输入并谨记。
  
  **[安装成功]**：浏览器访问你的服务器ip即可打开首页
  (nginx默认占用80端口,云服务器请先在控制台安全组添加该端口),
  注册用户admin自动成为管理员
  
  **[运维须知]**： 
  
  1.安装后项目位于/home/LDUOnlieJudge
  
  2.自动安装mysql5.7，管理员root@localhost(密码rootroot)，
  该项目专用用户lduoj@localhost(密码123456789)。
  
  **为保证安全性请及时修改**
  (注：需同时修改①ubuntu下mysql，②项目下.env文件数据库配置，③项目下judge/judge.conf，
  ④最后在项目根目录下执行`php artisan config:cache`)
  
  3.配置域名：在/etc/nginx/conf.d/lduoj.conf文件内，在`server_name`后面填域名。

# 备份
  ```
  bash /home/LDUOnlineJudge/install/ubuntu16.04/backup.sh
  ```
  **提示**：产生备份`/home/lduoj_update/lduoj_{日期}`，
  包含项目文件夹、数据库备份lduoj.sql、nginx配置文件lduoj.conf

# 更新

  建议更新前进行一次备份，以免数据丢失。
  ```
  git clone https://github.com/iamwinter/LDUOnlineJudge.git /home/lduoj_temp
  bash /home/LDUOnlineJudge/install/ubuntu16.04/update.sh
  ```

# 展示
