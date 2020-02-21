#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include<time.h>
#include<unistd.h>
#include<sys/stat.h>
#include<sys/types.h>
#include<sys/wait.h>
#include<sys/resource.h>
#include<mysql/mysql.h>

#include<string>

#define OJ_WT  0    //waiting
#define OJ_QI  1    //queueing
#define OJ_CI  2    //compiling
#define OJ_RI  3    //running
#define OJ_AC  4    //accepted
#define OJ_PE  5    //presentation error
#define OJ_WA  6    //wrong answer
#define OJ_TL  7    //time limit exceeded
#define OJ_ML  8    //time limit exceeded
#define OJ_OL  9    //output limit exceeded
#define OJ_RE 10    //runtime error
#define OJ_CE 11    //compile error
#define OJ_TC 12    //test completed
#define OJ_SK 13    //skipped
#define OJ_SE 14    //system error

#define COMPILE_TIME 10     //10s, compile time limit
#define COMPILE_FSIZE (10<<20)  //10MB,compile file size limit
#define COMPILE_MEM (512<<20)  //512MB,compile memory


const char *LANG[15]={"Main.c","Main.cpp","Main.java","Main.py"}; //判题文件名

char *db_host;
char *db_port;
char *db_user;
char *db_pass;
char *db_name;

MYSQL *mysql;    //数据库连接对象
MYSQL_RES *mysql_res;   //sql查询结果
MYSQL_ROW mysql_row;    //sql查询到的单行数据
char sql[256];   //暂存sql语句




//从文件读取内容，返回字符串指针
char *read_file(const char *filename)
{
    FILE *fp=fopen(filename,"r");
    fseek(fp,0L,SEEK_END);
    int ce_size=ftell(fp);  //获得内容长度
    fseek(fp,0L,SEEK_SET);
    char ch, *p, *str = new char[ce_size+1];
    for (p=str;(ch=fgetc(fp))!=EOF;*p++=ch);
    fclose(fp);
    return str;
}

//将字符串写入文件
void write_file(const char *str, const char *filename)
{
    FILE *fp=fopen(filename,"w");
    fprintf(fp,"%s",str);
    fclose(fp);
}


//结构体，一条提交记录
struct Solution{
    int id;
    char *judge_type;
    int time_limit;  //限制
    float memory_limit;
    int language;
    char *code;

    int result=1;
    int time=0; //MS 实际耗时
    float memory=0; //MB 实际内存
    float pass_rate=0;
    char *error_info=(char*)"";

    void load_solution(int sid) //从数据库读取提交记录，注：用到了全局mysql
    {
        sprintf(sql,"select `judge_type`,`time_limit`,`memory_limit`,`language`,`code` from solutions A inner join problems B on A.problem_id=B.id where A.id=%d",sid);
        if(mysql_real_query(mysql,sql,strlen(sql))!=0){
            printf("select failed!\n");
            exit(1);
        }
        mysql_res=mysql_store_result(mysql); //保存查询结果
        mysql_row=mysql_fetch_row(mysql_res); //读取
        this->id=sid;
        this->judge_type  =mysql_row[0];
        this->time_limit  =atoi(mysql_row[1]);
        this->memory_limit=atof(mysql_row[2]);
        this->language    =atoi(mysql_row[3]);
        this->code        =mysql_row[4];

        write_file(this->code,LANG[this->language]);
    }

    void update_result(int result)  //只更新result
    {
        this->result=result;
        sprintf(sql,"UPDATE solutions SET result=%d WHERE id=%d",this->result,this->id);
        mysql_real_query(mysql,sql,strlen(sql));
    }

    void update_solution()  //更新整个solution
    {
        sprintf(sql,"UPDATE solutions SET result=%d,time=%d,memory=%.2f,pass_rate=%.2f,judge_time=now() WHERE id=%d",
            this->result,this->time,this->memory,this->pass_rate,this->id); //更新
        mysql_real_query(mysql,sql,strlen(sql));
        if(this->result==OJ_CE){    //更新编译信息
            char *new_sql = new char[2*strlen(this->error_info)+35];
            char *p=new_sql;
            p+=sprintf(p,"UPDATE solutions SET error_info=\'");
            p+=mysql_real_escape_string(mysql,p,this->error_info,strlen(this->error_info));
            p+=sprintf(p,"\' where id=%d",this->id);
            mysql_real_query(mysql,new_sql,strlen(new_sql));
        }
    }
}solution;





//编译用户提交的代码
int compile()
{
    int pid;
    const char *CP_C[]  ={"gcc","Main.c",  "-o","Main","-Wall","-lm","--static","-std=c99",  "-fmax-errors=10","-DONLINE_JUDGE","-O2",NULL};
	const char *CP_CPP[]={"g++","Main.cpp","-o","Main","-Wall","-lm","--static","-std=c++11","-fmax-errors=10","-DONLINE_JUDGE","-fno-asm", NULL};
	const char *CP_JAVA[]={"javac","-J-Xms64m","-J-Xmx128m","-encoding","UTF-8","Main.java",NULL};

    if( (pid=fork()) == 0 ) //子进程编译
    {
        struct rlimit LIM;
        LIM.rlim_max=LIM.rlim_cur=COMPILE_TIME;
        setrlimit(RLIMIT_CPU, &LIM);  // cpu time limit; 10s
        LIM.rlim_max=LIM.rlim_cur=COMPILE_FSIZE;
        setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 10MB
        LIM.rlim_max=LIM.rlim_cur= solution.language==2 ? COMPILE_MEM<<2 : COMPILE_MEM; //java要扩大
        setrlimit(RLIMIT_AS, &LIM); //memory limit; c/c++ 512MB, java 2048MB
        alarm(COMPILE_TIME);  //定时

        freopen("ce.txt","w",stderr);
        switch(solution.language){
            case 0: execvp(CP_C[0],   (char * const *)CP_C); break;
            case 1: execvp(CP_CPP[0], (char * const *)CP_CPP); break;
            case 2: execvp(CP_JAVA[0],(char * const *)CP_JAVA); break;
        }
        exit(0);
    }
    else if(pid>0) //父进程
    {
        int status;
        waitpid(pid, &status, 0);
        return status;   //+:compile error
    }
    else
        return -1;  //-1:system error,
    return 0; //0:compile success,
}


//运行可执行文件
int judge(int sid)
{
    printf("judging!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!: %d\n",sid);
//    sleep(1);
    return OJ_TC;
}

int main (int argc, char* argv[])
{
    // 1. 读取参数
    if(argc!=6+1){
        printf("Judge arg number error!\n");
        exit(1);
    }
    db_host=argv[1];
    db_port=argv[2];
    db_user=argv[3];
    db_pass=argv[4];
    db_name=argv[5];
    int sid=atoi(argv[6]); //solution id

    // 2. 连接数据库
    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0); //连接
    if(!mysql){
        printf("Judge Error: Can't connect to database!\n\n");
        exit(1);
    }

    // 3. 创建临时文件夹并进入
    if(access("../run",0)==-1)
        mkdir("../run",0777);
    chdir("../run"); //进入工作目录
    mkdir(argv[6],0777);
    chdir(argv[6]); //进入sid临时文件夹

    // 4.读取+编译+判题
    solution.load_solution(sid);   //从数据库读取提交记录
    solution.update_result(OJ_CI); //update to compiling

    int CP_result=compile();
    if(CP_result==-1)//系统错误，正常情况下没有
    {
        printf("%d,compiling: System Error on fork();\n",sid);
        solution.result=OJ_SE;
    }
    else if(CP_result>0) //编译错误
    {
        printf("%d,compiling: Compile Error!\n",sid);
        solution.result=OJ_CE;
        solution.error_info = read_file("ce.txt");//将编译信息读到solution结构体变量
    }
    else    //编译成功，运行
    {
        printf("%d,Compiling successfully! begin with running\n",sid);
        solution.update_result(OJ_RI); //update to running
        solution.result = judge(sid);
    }

    // 5. 判题结果写回数据库
    solution.update_solution();    // update all of data

    // 6. 关闭数据库+删除临时文件夹
    mysql_close(mysql);
//    system("rm -rf `pwd`"); //删除该记录所用的临时文件夹
    return 0;
}
