## 介绍

OnlineJudge判题服务使用[go-judge](https://github.com/criyle/go-judge)。

## 构建多平台镜像

- 先创建Buildx

```bash
docker buildx create --use --name builder
```

- 查验Buildx是否创建成功

```bash
docker buildx inspect --bootstrap
```
```
Name:   arm_builder
Driver: docker-container

Nodes:
Name:      builder0
Endpoint:  unix:///var/run/docker.sock
Status:    running
Platforms: linux/arm64, linux/amd64, linux/ppc64le, linux/s390x, linux/386, linux/arm/v7, linux/arm/v6

```
- 构建适用于不同平台的镜像

```bash
docker buildx build --platform linux/amd64,linux/arm64,linux/arm/v7 --push -t winterant/go-judge:1.x .
```
`--platform`表示要构建什么平台的镜像，`--push`直接到docker hub上，当使用`docker pull`拉取镜像时会直接根据平台来自动选择镜像。
