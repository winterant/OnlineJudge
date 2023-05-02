<?php

// 1.x版本语言配置
// return [
//     0  => 'C (C17 -O2)',
//     1  => 'C++ (C++17 -O2)',
//     2  => 'Java (OpenJDK 8)',
//     3  => 'Python3.8',
// ];

// 2.x新版本，其实就是读取judge/language.php中配置的语言
return array_map(function ($v) {
    return $v['name'];
}, include(config_path('judge/language.php')));
