<?php

// 默认的编译配置
$defaultCompile = [
    'cpuLimit' => 10000000000,  // ns=10s
    'memoryLimit' => 256 << 20, // B=256MB
    'procLimit' => 8            // >=3
];

// 默认的运行配置
$defaultRun = [
    'command' => 'Main',
    'stdoutMax' => 64 << 20, // 64MB
    'stderrMax' => 10 << 10, // 10KB
    'procLimit' => 8,
];

// 生成最终配置
return [
    0 => [
        'name' => 'C (C17 -O2)',
        'filename' => 'Main.c',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/gcc Main.c -std=c17 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    1 => [
        'name' => 'C++ (C++98 -O2)',
        'filename' => 'Main.cc',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++98 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    4 => [
        'name' => 'C++ (C++11 -O2)',
        'filename' => 'Main.cc',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++11 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    5 => [
        'name' => 'C++ (C++14 -O2)',
        'filename' => 'Main.cc',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++14 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    6 => [
        'name' => 'C++ (C++20 -O2)',
        'filename' => 'Main.cc',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++20 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],

    2 => [
        'name' => 'Java (OpenJDK 8)',
        'filename' => 'Main.java',
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/binjavac -J-Xms64m -J-Xmx256m -encoding UTF-8 Main.java',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    3 => [
        'name' => 'Python3.8',
        'filename' => 'Main.py',
        'compile' => null, // python 不需要编译
        'run' => array_merge($defaultRun, [
            'command' => 'python3 Main.py',
        ])
    ],
];
