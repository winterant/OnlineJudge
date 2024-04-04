module.exports = {
    title: 'SparkOJ / LDUOJ',
    description: 'Sparks of Fire Online Judge (SparkOJ / LDUOJ)',
    publicPath: './',
    base: '/',
    themeConfig: {
        logo: '/favicon.ico',
        lastUpdated: '上次更新', // string | boolean
        nav: [
            { text: '首页', link: '/' },
            { text: '文档', link: '/deploy/' },
            { text: 'GitHub', link: 'https://github.com/winterant/OnlineJudge', target: '_blank' },
        ],
        sidebar: [
            {
                title: '新手入门',
                // path: '/deploy/',  // 点击标题时展示的页面
                collapsable: false,
                sidebarDepth: 1,
                children: [
                    '/deploy/',
                    '/deploy/deploy.md',
                    '/deploy/network.md',
                    '/deploy/email.md'
                ]
            },
            {
                title: '使用说明',
                collapsable: false,
                sidebarDepth: 1,
                children: [
                    '/web/',
                    '/web/judge.md',
                    '/web/spj.md',
                    '/web/result.md',
                ]
            },
            {
                title: '开发手册',
                collapsable: false,
                sidebarDepth: 2,
                children: [
                    '/develop/'
                ],
                initialOpenGroupIndex: -1 // 可选的, 默认值是 0
            }
        ]
    }
}
