
# Docker相关

+ 构建镜像

  ```shell script
  git clone https://github.com/iamwinter/LDUOnlineJudge.git
  cp LDUOnlineJudge/install/docker/{Dockerfile,.dockerignore} ./
  docker build  -t lduoj:local .
  rm -rf ./{LDUOnlineJudge,Dockerfile,.dockerignore}
  ```

+ 复制镜像

  ```shell script
  docker tag lduoj:local iamwinter/lduoj:latest
  ```

+ 上传镜像

  ```shell script
  docker login
  docker push iamwinter/lduoj:latest
  ```
