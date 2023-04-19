<?php

// 默认的环境变量
$defaultEnv=[
    'PATH=/usr/bin:/bin'
];

// 默认的编译配置
$defaultCompile = [
    'compiled_filename'=>'Main',// 编译好的程序文件名，一般均为Main，java例外为**.class
    'cpuLimit' => 10000000000,  // ns=10s
    'memoryLimit' => 256 << 20, // B=256MB
    'procLimit' => 8            // >=3, golang>=32
];

// 默认的运行配置
$defaultRun = [
    'command' => './Main',
    'stdoutMax' => 64 << 20, // 64MB
    'stderrMax' => 10 << 10, // 10KB
    'procLimit' => 8,
    'limit_boost'=>1 // 运行时间、内存限制的放大倍数；C/C++之外的语言应当为2
];

// 生成最终配置
return [
    0 => [
        'name' => 'C (C17 -O2)',
        'filename' => 'Main.c',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/gcc Main.c -std=c17 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],

    // 5~8,1:C++
    5 => [
        'name' => 'C++ (C++98 -O2)',
        'filename' => 'Main.cc',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++98 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    6 => [
        'name' => 'C++ (C++11 -O2)',
        'filename' => 'Main.cc',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++11 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    7 => [
        'name' => 'C++ (C++14 -O2)',
        'filename' => 'Main.cc',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++14 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    1 => [
        'name' => 'C++ (C++17 -O2)',
        'filename' => 'Main.cc',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++98 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    8 => [
        'name' => 'C++ (C++20 -O2)',
        'filename' => 'Main.cc',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cc -std=c++20 -O2 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],

    2 => [
        'name' => 'Java (OpenJDK 8)',
        'filename' => 'Main.java',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/javac -J-Xms64m -J-Xmx256m -encoding UTF-8 Main.java',
            'compiled_filename'=>'Main.class',
            'procLimit' => 32
        ]),
        'run' => array_merge($defaultRun, [
            'command' => '/usr/bin/java Main',
            'procLimit' => 32,
            'limit_boost'=>2
        ])
    ],

    3 => [
        'name' => 'Python3',
        'filename' => 'Main.py',
        'env'=>$defaultEnv,
        'compile' => null, // python 不需要编译
        'run' => array_merge($defaultRun, [
            'command' => 'python3 Main.py',
            'limit_boost'=>2
        ])
    ],

    10 => [
        'name' => 'Golang',
        'filename' => 'Main.go',
        'env'=> array_merge($defaultEnv,[
            'GOPATH=/w',
            'GOCACHE=/tmp/'
        ]),
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/go build Main.go',
            'procLimit'=>32
        ]),
        'run' => array_merge($defaultRun, [
            'procLimit'=>32,
            'limit_boost'=>2
        ])
    ]
];
