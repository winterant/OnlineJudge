module.exports = {
    title: 'LDUOJ',
    description: 'Ludong University Online Judge',
    publicPath: './',
    base: '/OnlineJudge/',
    themeConfig: {
        logo: '/favicon.ico',
        lastUpdated: '上次更新: ', // string | boolean
        nav: [
            { text: '首页', link: '/' },
            { text: '文档', link: '/deploy/' },
            { text: 'GitHub', link: 'https://github.com/winterant/OnlineJudge', target: '_blank' },
        ],
        sidebar: [
            {
                title: '入门',
                path: '/deploy/',
                collapsable: true,
                sidebarDepth: 1,
                children: [
                    '/deploy/',
                    '/deploy/deploy.md',
                    '/deploy/network.md'
                ]
            },
            {
                title: '使用说明',
                path: '/web/',
                collapsable: true,
                sidebarDepth: 1,
                children: [
                    '/web/',
                    '/web/judge.md',
                    '/web/result.md',
                    '/web/admin.md',
                    '/web/spj.md'
                ]
            },
            {
                title: '开发',
                children: [
                    '/develop/'
                ],
                initialOpenGroupIndex: -1 // 可选的, 默认值是 0
            }
        ]
    }
}
