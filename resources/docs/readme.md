# Online Judge Docs

> 该仓库是[lduoj](https://github.com/winterant/OnlineJudge)中文文档源码。

## 开发环境

[VuePress](https://vuepress.vuejs.org/zh/guide/getting-started.html)

```bash
git clone https://github.com/winterant/OnlineJudge.git
cd OnlineJudge
git checkout docs
cd resources/docs/
npm init
npm install -D vuepress
```

启动开发环境
```bash
npm run src:dev
```

打包生产环境，并移动到根目录`docs/`以便部署到github pages
```bash
npm run src:build
rm -rf ../../docs
mv src/.vuepress/dist ../../docs
```
