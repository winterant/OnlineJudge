# Online Judge Docs

## 开发环境

[参考文档](https://vuepress.vuejs.org/zh/guide/getting-started.html)

```bash
git clone -b docs https://github.com/winterant/OnlineJudge.git
cd OnlineJudge
yarn init # npm init
yarn add -D vuepress # npm install -D vuepress
```

启动开发环境
```bash
yarn src:dev # npm run src:dev
```

打包生产环境
```bash
yarn src:build # npm run src:build
```

部署github pages
```bash
rm -rf docs
mv src/.vuepress/dist docs
```
