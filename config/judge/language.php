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
    'limit_amplify'=>1 // 运行时间、内存限制的放大倍数；C/C++之外的语言应当为2
];

// 生成最终配置
return [
    0 => [
        'name' => 'C17',
        'filename' => 'Main.c',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/gcc Main.c -std=c17 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],

    // 5~8,1:C++
    5 => [
        'name' => 'C++98',
        'filename' => 'Main.cpp',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cpp -std=c++98 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    6 => [
        'name' => 'C++11',
        'filename' => 'Main.cpp',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cpp -std=c++11 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    7 => [
        'name' => 'C++14',
        'filename' => 'Main.cpp',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cpp -std=c++14 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    1 => [
        'name' => 'C++17',
        'filename' => 'Main.cpp',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cpp -std=c++98 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],
    8 => [
        'name' => 'C++20',
        'filename' => 'Main.cpp',
        'env'=>$defaultEnv,
        'compile' => array_merge($defaultCompile, [
            'command' => '/usr/bin/g++ Main.cpp -std=c++20 -DONLINE_JUDGE -w -fmax-errors=1 -lm -o Main',
        ]),
        'run' => array_merge($defaultRun, [])
    ],

    2 => [
        'name' => 'Java8',
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
            'limit_amplify'=>2
        ])
    ],

    3 => [
        'name' => 'Python3',
        'filename' => 'Main.py',
        'env'=>$defaultEnv,
        'compile' => null, // python 不需要编译
        'run' => array_merge($defaultRun, [
            'command' => 'python3 Main.py',
            'limit_amplify'=>2
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
            'limit_amplify'=>2
        ])
    ]
];