<?php

// return [
//     0  => 'C (C17 -O2)',

//     1  => 'C++ (C++98 -O2)',
//     4  => 'C++ (C++11 -O2)',
//     5  => 'C++ (C++14 -O2)',
//     6  => 'C++ (C++20 -O2)',

//     2  => 'Java (OpenJDK 8)',
//     3  => 'Python3.8',
// ];

// 其实就是读取judge/language.php中配置的语言
return array_map(function ($v) {
    return $v['name'];
}, include(config_path('judge/language.php')));
