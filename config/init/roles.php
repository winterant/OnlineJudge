<?php
// 预置角色

return [
    'admin' => ['admin'],
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
];
