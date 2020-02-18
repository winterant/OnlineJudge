#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include<time.h>
#include<mysql/mysql.h>
#include<unistd.h>
#include<sys/types.h>
#include<sys/wait.h>


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


char *db_host;
char *db_port;
char *db_user;
char *db_pass;
char *db_name;

const char *lang[15]={"c","cpp","java","py"}; //判题语言后缀

MYSQL *mysql;    //数据库连接对象
MYSQL_RES *mysql_res;   //sql查询结果
MYSQL_ROW mysql_row;    //sql查询到的单行数据
char sql[256];   //暂存sql语句

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
    char *error_info=NULL;

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
    }

    void update_result(int result)  //只更新result
    {
        this->result=result;
        sprintf(sql,"UPDATE solutions SET result=%d WHERE id=%d",this->result,this->id);
        mysql_real_query(mysql,sql,strlen(sql));
    }

    void update_solution()  //更新整个solution
    {
        sprintf(sql,"UPDATE solutions SET result=%d,time=%d,memory=%f,pass_rate=%f,error_info='%s',judge_time=now() WHERE id=%d",
            this->result,this->time,this->memory,this->pass_rate,this->error_info,this->id); //更新
        mysql_real_query(mysql,sql,strlen(sql));
    }
}solution;



//编译用户提交的代码
int compile()
{
    const char *CP_C[]  ={"gcc","Main.c",  "-o","Main","-Wall","-lm","--static","-std=c99",  "-fmax-errors=10","-DONLINE_JUDGE","-O2",NULL};
	const char *CP_CPP[]={"g++","Main.cpp","-o","Main","-Wall","-lm","--static","-std=c++11","-fmax-errors=10","-DONLINE_JUDGE","-fno-asm", NULL};
	const char *CP_JAVA[]={"javac","-J-Xms64m","-J-Xmx128m","-encoding","UTF-8","Main.java",NULL};

    return 0;
}


//运行可执行文件
int judge(int sid)
{
    printf("judging!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!: %d\n",sid);
    sleep(1);
    printf("judging end   !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!: %d\n",sid);
    return OJ_AC;
}

int main (int argc, char* argv[])
{
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

    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0); //连接
    if(!mysql){
        printf("Judge Error: Can't connect to database!\n\n");
        exit(1);
    }

    solution.load_solution(sid);   //loading the solution whose id is sid
    solution.update_result(OJ_CI); //update to compiling
    compile();
    solution.update_result(OJ_RI); //update to running
    solution.result = judge(sid);
    solution.update_solution();    // update all of data
    mysql_close(mysql);
    return 0;
}
