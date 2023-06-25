# Online Judge Docs

## 开发环境

[VuePress](https://vuepress.vuejs.org/zh/guide/getting-started.html)

```bash
git clone -b docs https://github.com/winterant/OnlineJudge.git
cd OnlineJudge
npm init
npm install -D vuepress
```

启动开发环境
```bash
npm run src:dev
```

打包生产环境，并移动到`/docs/`以便部署到github pages
```bash
npm run src:build
rm -rf docs
mv src/.vuepress/dist docs
```
