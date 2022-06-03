<?php

// 该文件为权限列表
return [
    "admin"      => "最高管理员(所有权限)",
    "teacher"    => "管理题目所有功能<br>" .
                    "管理问题标签<br>" .
                    "管理问题讨论版<br>" .
                    "管理测试数据<br>" .
                    "管理竞赛<br>" .
                    "管理课程（群组）",

    // 后台管理首页权
    "admin.home"        => "进入后台管理页面",

    // 判题端控制权
    "admin.cmd_polling" => "启动/关闭判题端",

    // 公告管理
    "admin.notice"      => "管理公告(所有功能)",

    // 账户管理
    "admin.user"        => "管理用户(所有功能)",
    "admin.user.edit"   => "修改任意用户的个人信息",

    // 题库
    "admin.problem"         => "管理题目(所有功能)",
    "admin.problem.edit"    => "新增、编辑题目",
    "admin.problem.list"    => "查看题目列表、题目内容",
    "admin.problem.data"    => "查看/管理评测数据",
    "admin.problem.rejudge" => "重判用户代码",
    "admin.problem.tag"     => "管理题目标签",
    "admin.problem.import_export" => "导入导出题目",
    "admin.problem.discussion"    => "管理题目讨论版内容",
    "admin.problem.solution"      => "查看任意用户提交的代码",

    // 竞赛
    "admin.contest"          => "管理竞赛(所有功能)",
    "admin.contest.balloon"  => "竞赛气球派送",
    "admin.contest.category" => "管理竞赛类别",

    // 群组管理
    "admin.group"       => "管理课程（群组）(所有功能)",
    "admin.group.edit"  => "新增、编辑课程（群组）",

    // 系统相关
    "admin.setting"    => "修改系统设置、升级OJ"
];
