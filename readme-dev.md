<h1 align="center">开发者文档</h1>

# 🌏 Docker镜像发布

安装docker请参考[官方文档](https://yeasy.gitbook.io/docker_practice/install/ubuntu#shi-yong-jiao-ben-zi-dong-an-zhuang)

+ 将本项目构建为docker镜像

  ```bash
  git clone https://github.com/winterant/LDUOnlineJudge.git
  cd LDUOnlineJudge
  docker build -t lduoj:latest .
  ```
  注意：Windows系统下请先将换行符`\r\n`统一转换为`\n`再构建镜像。
