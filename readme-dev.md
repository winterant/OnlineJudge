<h1 align="center">开发者文档</h1>

# 🧱 整体架构

+ `routes/web.php`：路由转发文件，定义了全站路由。
+ `config/oj/`：含本OJ自定义的配置文件。
+ `app/Http/`：后端控制器`Controllers`、中间件`Middleware`等程序。
+ `resources/views/`：前端html代码。
+ `resources/lang/`：网页文字语言翻译文件。
+ `public/`：网页访问入口`index.php`，js、css文件和web插件。
+ `storage/app/`：保存题目数据、文件等。
+ `storage/app/public/`：保存静态文件(如图片)等。软连接到`public/storage`供网页访问。
+ `judge/`：判题程序，与laravel框架无关。
+ `install/`：用于安装本OJ，与laravel框架无关。
+ `.env.example`：配置文件，含数据库连接信息、判题设置等。复制为`.env`生效。

# 🌏 Docker镜像发布

安装docker请参考[官方文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)

+ 将本项目构建为docker镜像
  
  ```bash
  git clone https://github.com/winterant/LDUOnlineJudge.git
  cd LDUOnlineJudge
  docker build -f install/docker/Dockerfile -t lduoj:latest .  # 末尾有点
  ```
  注意：Windows系统下请先将换行符`\r\n`统一转换为`\n`再构建镜像。

+ 将镜像发布到`dockerhub`

  ```bash
  docker push winterant/lduoj:latest
  ```
