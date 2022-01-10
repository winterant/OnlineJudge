<h1 align="center">Ludong University Online Judge</h1>

> 鲁东大学程序设计在线测评系统与考试平台  
github主仓库: <https://github.com/winterant/LDUOnlineJudge>  
gitee同步仓库: <https://gitee.com/winterantzhao/LDUOnlineJudge>  
中国镜像仓库: <https://github.com.cnpmjs.org/winterant/LDUOnlineJudge>  

# 💡 快速了解

+ 官方网站[http://icpc.ldu.edu.cn](http://icpc.ldu.edu.cn)；
+ 演示网站[https://lduoj.top](https://lduoj.top)；
+ 截屏展示[点击跳转](https://blog.csdn.net/winter2121/article/details/105294224)；

**前台**

+ 首页；公告/新闻，本周榜，上周榜；
+ 状态；用户提交记录与判题结果；
+ 问题；题库（支持编程题、代码填空题）；
+ 竞赛；题目(选自题库)，排名(ACM,OI)可封榜，**赛后补题榜**，公告栏，气球派送；
+ 排名；用户解题排行榜，可按年/月/周/日查询。

**后台**

+ 判题进程；启动/停止linux判题端进程；
+ 公告新闻；用户访问首页可见；
+ 用户管理；**账号权限分配**，批量生成账号，**黑名单**；
+ 题目管理；增改查，公开/隐藏，重判结果，**导入与导出(兼容hustoj)**；
+ 竞赛管理；增删查改，公开/隐藏；
+ 系统配置；修改网站名称，打开/关闭一些全局功能，**中英文切换**，系统在线升级等。

# 🔨 项目部署

```bash
docker run -d -p 8080:80 \
    -v ~/lduoj_docker:/volume \
    --restart always \
    --name lduoj \
    winterant/lduoj
```

+ 若镜像下载过慢，请[更换docker镜像源](https://blog.csdn.net/winter2121/article/details/107399812)后重试；
+ `-p`指定`8080`作为对外端口，访问`http://ip:8080`进入首页；您可在宿主机[配置域名与端口](https://blog.csdn.net/winter2121/article/details/107783085)；
+ `-v`指定`~/lduoj_docker`作为宿主机挂载目录；
+ **注册账号admin自动成为管理员**。

# 🔄 项目升级
+ 方式一，更新容器内的源码
```bash
docker exec -it lduoj /bin/bash
git clone https://github.com/winterant/LDUOnlineJudge.git ojup
# git clone https://gitee.com/winterantzhao/LDUOnlineJudge.git ojup
bash ojup/install/ubuntu/update.sh
```

+ 方式二，拉取最新的docker镜像，启动新容器。

# 💿 项目迁移（备份）

1.在**原主机**将文件夹`~/lduoj_docker`（即容器内`/volume`）打包，发送到**新主机**

```bash
tar -zcvf volume.tar.gz /volume     # 打包
scp -P 22 volume.tar.gz root@ip:~/  # 发送到新主机`~/`下；也可以自行拷贝
```

2.在新主机解压收到的压缩文件

```bash
tar -zxvf volume.tar.gz
```

3.在新主机[启动容器](#项目部署)，注意参数`-v`改为挂载步骤2解压出的目录(绝对路径)

# 📝 判题端使用说明

+ 启动方式

  A. 网页端进入后台首页，即可点击相应按钮启动/重启/停止判题端  
  B. 通过终端命令启动判题端：`bash judge/startup.sh`

+ 判题端配置（`judge/config.sh`）：
  ```shell
  JG_DATA_DIR=storage/app/data  # 测试数据所在目录，**请勿修改!**
  JG_NAME="Master"              # 判题机名称，可修改
  JG_MAX_RUNNING=1              # 最大并行判题进程数；建议值 = 剩余内存(GB) / 2
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
[zhiyul/switch](https://github.com/notiflix/Notiflix)  
[codemirror](https://codemirror.net/)  
[highlight.js](https://highlightjs.org/)  

# 💰 捐助

一杯咖啡就能增加我写代码的动力~ 3Q~
<div align="center">
  <img src="install/images/alipay.jpg" height=300>
  <img src="install/images/wechatpay.jpg" height=300>
</div>

# 📜 开源许可

LDUOnlineJudge is licensed under the
**[GNU General Public License v3.0](https://github.com/winterant/LDUOnlineJudge/blob/master/LICENSE)**.
