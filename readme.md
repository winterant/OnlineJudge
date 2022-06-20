<h1 align="center">Ludong University Online Judge</h1>

> 鲁东大学程序设计在线测评系统与考试平台  
github主仓库: <https://github.com/winterant/LDUOnlineJudge>  
gitee同步仓库: <https://gitee.com/wrant/LDUOnlineJudge>  

# 💡 快速了解

+ 官方网站：[https://icpc.ldu.edu.cn](http://icpc.ldu.edu.cn)；
+ 演示网站：[https://lduoj.top](https://lduoj.top)；
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
docker run -d -p 8080:80 -v ~/lduoj/volume:/volume --name lduoj winterant/lduoj
```

+ 安装docker请参考[官方文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)
+ `-p`指定`8080`作为宿主机对外端口，访问`http://ip:8080`进入首页；您可在宿主机[配置域名](https://blog.csdn.net/winter2121/article/details/107783085)；
+ `-v`指定`~/lduoj/volume`作为宿主机挂载目录；
+ **注册账号admin自动成为管理员**。

# 🚗 升级

```bash
docker exec -it lduoj bash
# git clone https://github.com/winterant/LDUOnlineJudge.git ojup
git clone https://gitee.com/wrant/LDUOnlineJudge.git ojup
bash ojup/update.sh
```

# 💿 备份/迁移

## 备份
1. 进入容器，备份数据库；
    ```bash
    docker exec -it lduoj bash
    bash install/mysql/database_backup.sh
    ```
2. 将文件夹`/volume`打包，自行拷贝备份；这一步在宿主机、容器内均可；**打包过程中，不要关闭终端**；
    ```bash
    tar -cf - /volume | pigz -p $(nproc) > volume.tar.gz
    ```
## 恢复
1. 在宿主机找一个位置，解压出`/volume`；
    ```bash
    tar -zxvf volume.tar.gz
    ```
2. 删除旧容器，并重新部署项目(创建容器)；注意参数`-v`挂载路径是上一步解压出的绝对路径；
    ```bash
    docker rm -f lduoj  # 强制删除旧容器（如果有）
    docker run -d -p 8080:80 -v ~/lduoj/volume:/volume --name lduoj winterant/lduoj
    ```
3. 进入容器，恢复数据库；这一步不做也可以，但数据无价，为了保险起见，执行一下；
    ```bash
    docker exec -it lduoj bash
    bash install/mysql/database_recover.sh
    ```

# 📝 判题端使用说明

+ 启动方式

  A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端
  B. 通过终端命令启动判题端：`bash judge/startup.sh`

+ 判题端配置（`judge/config.sh`）：
  ```shell
  JG_DATA_DIR=storage/app/data  # 测试数据所在目录，**请勿修改!**
  JG_NAME="Master"              # 判题机名称，可修改
  JG_MAX_RUNNING=2              # 最大并行判题进程数；建议值 = 剩余内存(GB) / 2
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
