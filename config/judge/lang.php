<?php

/**
 * 该配置文件用于显示各个语言的名字，编号与语言的对应关系等。
 * 该文件无需修改，请在judge/language.php中配置编程语言。
 */

// 1.x版本语言配置
// return [
//     0  => 'C (C17 -O2)',
//     1  => 'C++ (C++17 -O2)',
//     2  => 'Java (OpenJDK 8)',
//     3  => 'Python3.8',
// ];

// 2.x新版本，直接读取judge/language.php中配置的语言名称
return array_map(function ($v) {
    return $v['name'];
}, include(config_path('judge/language.php')));
