<?php

// 该文件为权限列表
return [
    "admin"      => "最高管理员（所有权限）",
    "teacher"    => "新增、编辑题目<br>" .
                    "管理问题标签<br>" .
                    "管理问题讨论版<br>" .
                    "管理测试数据<br>" .
                    "管理竞赛<br>" .
                    "管理课程（群组）",

    // 后台管理首页权
    "admin.home"        => "进入后台管理",

    // 判题端控制权
    "admin.cmd_polling" => "启动/关闭判题端（前提：admin.home）",

    // 公告管理
    "admin.notice"      => "管理公告",

    // 账户管理
    "admin.user"        => "管理用户",
    "admin.user.edit"   => "修改任意用户的个人信息",

    // 题库
    "admin.problem"      => "管理题目（包括题目、标签、导入导出等一切题库相关操作）",
    "admin.problem.edit" => "新增、编辑题目",
    "admin.problem.list" => "查看题目列表、题目内容",
    "admin.problem.tag"  => "管理题目标签",
    "admin.problem.import_export" => "导入导出题目",
    "admin.problem.discussion" => "管理题目讨论版内容",
    "admin.problem.solution"   => "查看任意用户提交的代码",

    // 竞赛
    "admin.contest"          => "管理竞赛",
    "admin.contest.balloons" => "竞赛气球派送",

    // 群组管理
    "admin.group"       => "管理课程（群组）",
    "admin.group.edit"  => "新增、编辑课程（群组）",

    // 系统相关
    "admin.settings"    => "修改系统设置",
    "admin.upgrade"     => "执行系统升级",
];
