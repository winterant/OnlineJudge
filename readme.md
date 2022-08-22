<h1 align="center">Ludong University Online Judge</h1>

> 鲁东大学程序设计在线测评系统与考试平台  
github主仓库: <https://github.com/winterant/LDUOnlineJudge>  
gitee同步仓库: <https://gitee.com/wrant/LDUOnlineJudge>  

# 💡 快速了解

+ 官方网站：[https://icpc.ldu.edu.cn](http://icpc.ldu.edu.cn)；
+ 截屏展示：[点击跳转](https://blog.csdn.net/winter2121/article/details/105294224)；

**前台**

+ 首页；公告/新闻，本周榜，上周榜；
+ 状态；用户提交记录与判题结果；
+ 问题；题库（支持编程题、代码填空题）；
+ 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送；
+ 排名；用户解题排行榜。

**后台**

+ 判题进程；启动/停止linux判题端进程；
+ 公告新闻；用户访问首页可见；
+ 用户管理；**账号权限分配**，批量生成账号，**黑名单**；
+ 题目管理；增改查，公开/隐藏，重判结果，**导入与导出(兼容hustoj)**；
+ 竞赛管理；增删查改，公开/隐藏；
+ 系统配置；修改网站名称，打开/关闭一些全局功能，**中英文切换**，系统在线升级等。

# 🔨 部署

```bash
docker run -d -p 8080:80 \
    -v lduoj_storage:/LDUOnlineJudge/storage \
    -v lduoj_database:/var/lib/mysql/lduoj \
    --name lduoj \
    winterant/lduoj:22.09
```

+ 访问首页`http://ip:8080`；可在宿主机[配置域名](https://blog.csdn.net/winter2121/article/details/107783085)；
+ **注册账号admin自动成为管理员**；
+ 你必须部署[judge0-v1.13.0](https://github.com/judge0/judge0/releases/tag/v1.13.0)才能判题；

# 🚗 升级

```bash
docker exec -it lduoj bash  # 进入容器
git clone https://gitee.com/wrant/LDUOnlineJudge.git ojup
bash ojup/install/update.sh
```

# 💿 备份/迁移

【提示】若要迁移到其它机器，请务必先升级到最新。

## 备份
1. [可选]进入容器，备份数据库（以防万一）；
    ```bash
    docker exec -it lduoj bash
    bash install/mysql/database_backup.sh /LDUOnlineJudge/storage/backup/db.sql
    ```
2. [备份数据卷](http://shouce.jb51.net/docker_practice/data_management/management.html) [`lduoj_storage`, `lduoj_database`]

## 恢复
1. [恢复数据卷](http://shouce.jb51.net/docker_practice/data_management/management.html) [`lduoj_storage`, `lduoj_database`]
2. 重新[部署](#🔨-部署)
3. [可选]如果数据库未恢复，可进入容器，手动恢复数据库；
    ```bash
    docker exec -it lduoj bash
    bash install/mysql/database_recover.sh
    ```

# 💝 致谢

[zhblue/hustoj](https://github.com/zhblue/hustoj)  
[judge0](https://judge0.com/)  
[sim](https://dickgrune.com/Programs/similarity_tester/)  
[laravel-6.0](https://laravel.com/)  
[bootstrap-material-design](https://fezvrasta.github.io/bootstrap-material-design/)  
[jquery-3.4.1](https://jquery.com/)  
[font-awesome](http://www.fontawesome.com.cn/)  
[ckeditor-5](https://ckeditor.com/ckeditor-5/)  
[MathJax](https://www.mathjax.org/)  
[notiflix/Notiflix](https://github.com/notiflix/Notiflix)  
[weatherstar/switch](https://github.com/weatherstar/switch)  
[codemirror](https://codemirror.net/)  
[highlight.js](https://highlightjs.org/)  

# 📜 开源许可

LDUOnlineJudge is licensed under the
**[GNU General Public License v3.0](https://github.com/winterant/LDUOnlineJudge/blob/master/LICENSE)**.
