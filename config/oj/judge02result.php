<?php
// judge0的判题结果编号 => web端判题结果编号
return [
    1 => 1, // In Queue     => Queueing
    2 => 2, // Processing   => Running
    3 => 4, // Accepted
    4 => 6, // Wrong Answer
    5 => 7, // Time Limit Exceeded
    6 =>11, // Compilation Error
    7 =>10, // Runtime Error (SIGSEGV) => 段错误(访问非法内存)
    8 => 9, // Runtime Error (SIGXFSZ) => 输出超限
    9 =>10, // Runtime Error (SIGFPE)  => 算术溢出
    10=>10, // Runtime Error (SIGABRT) => 地址越界
    11=>10, // Runtime Error (NZEC)    => 退出码非零, Memory Limit Exceeded或者数组越界
    12=>10, // Runtime Error (Other)   => 其他RE
    13=>14, // Internal Error          => System Error (服务器错误)
    14=>14, // Exec Format Error       => System Error (脚本执行失败)
];
