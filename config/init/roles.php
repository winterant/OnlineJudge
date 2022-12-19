<?php
// 预置角色

return [
    // 超级管理员
    'admin' => ['admin'],
    // 教师
    'teacher' => [
        'admin.view',
        // 题目
        'admin.problem.view',
        'admin.problem.create',
        // 竞赛
        'admin.contest.view',
        'admin.contest.create',
        // 群组
        'admin.group.view',
        'admin.group.create',
    ],
    // 代码查看者
    'code viewer' => [
        'admin.solution.view'
    ],
    // 竞赛气球派送员
    'contest balloon postman' => [
        'admin.contest_balloon'
    ]
];
