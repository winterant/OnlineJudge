# 判题服务

你可以参考判题服务[开源代码](https://github.com/winterant/judge)深入了解判题逻辑；
你的代码提交后，将由判题服务运行并评测。判题服务接收到你提交的代码，将经历以下步骤

## 编译
   
- C语言
```bash
gcc Main.c -o Main -Wall -lm --static -std=c17 -O2 -fmax-errors=5
```

- C++
```bash
g++ Main.cpp -o Main -Wall -lm --static -std=c++17 -O2 -fmax-errors=5 -fno-asm
```

- Java

你提交的代码中，必须包含名为`Main`的类，并包含静态成员方法`main`，例如
```java
public class Main{
    public static void main(String [] args){
        // Write your solution.
    }
}
```
编译命令：
```bash
javac -J-Xms64m -J-Xmx256m -encoding UTF-8 Main.java
```

## 运行

判题服务首先会获取出题人提供的测试数据。
对于每一组测试数据，选手程序将被运行一次，获取其标准输出用于答案对比。

## 答案对比

答案对比有两种方式。

1. 文本对比；你的标准输出和标注答案进行逐字符比较，如不一致，则判为`Wrong Answer`（答案错误）或其他错误类型；
2. 特判对比；出题人提供特判程序来评测你的标准输出，评测结果取决于出题人的特判程序；具体可参考[特判方法](./spj.md)；

## 代码查重

判题服务使用开源查重工具`sim`将选手代码与以往提交进行查重。默认情况下，查重结果只有管理员可以看到。
