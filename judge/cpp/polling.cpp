#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include<time.h>
#include<mysql/mysql.h>
#include<unistd.h>
#include<sys/types.h>
#include<sys/wait.h>
#include<signal.h>

#define OJ_WT  0    //waiting
#define OJ_QI  1    //queueing
#define OJ_CI  2    //compiling
#define OJ_RI  3    //running
#define OJ_AC  4    //accepted

char *db_host;
char *db_port;
char *db_user;
char *db_pass;
char *db_name;
char *JG_DATA_DIR;   //测试数据所在目录
int  max_running;    //最大同时判题数
char *JG_NAME;

MYSQL *mysql;        //数据库连接对象
MYSQL_RES *mysql_res;//sql查询结果
MYSQL_ROW mysql_row; //sql查询到的单行数据
char sql[256];       //暂存sql语句

int Recv_SIGTERM=0; // 标记是否接收到了终止信号


void stop_polling(int signo)
{
    printf("[signal] Received signal %d\n",signo);
    Recv_SIGTERM=1;
}

int get_a_waiting_solution() //从solutions表读取1个待判编号
{
    // 1. 防并发；即开始一个事务，确保下面的查询和修改具有原子性
    sprintf(sql,"begin");
    mysql_real_query(mysql,sql,strlen(sql));

    // 2. 查询1个waiting solution
    int sid=-1;
    sprintf(sql,"SELECT id FROM solutions WHERE result=%d ORDER BY id ASC limit 1",OJ_WT);
    mysql_real_query(mysql,sql,strlen(sql));
    mysql_res=mysql_store_result(mysql);
    mysql_row=mysql_fetch_row(mysql_res);
    if(mysql_row)
        sid=atoi(mysql_row[0]);   //查询到1个waiting solution
    mysql_free_result(mysql_res); //必须释放结果集，因为它是malloc申请在堆里的内存

    // 3. 更新状态为Queueing
    if(sid!=-1)
    {
        printf("Judger named [%s] is gonna judge solution %d\n",JG_NAME,sid);
        sprintf(sql,"UPDATE solutions SET result=%d,judger='%s' WHERE id=%d",OJ_QI,JG_NAME,sid);
        mysql_real_query(mysql,sql,strlen(sql));
    }

    // 4. 提交事务；并返回solution id
    sprintf(sql,"commit");
    mysql_real_query(mysql,sql,strlen(sql));
    return sid;
}

void polling()  //轮询数据库收集待判提交
{
    int running_cnt=0,sid;     //正在判题数,solution id
    int pid,did;
    char sid_str[12];
    sprintf(sql,"UPDATE solutions SET result=0 where result<=%d and judger='%s'",OJ_RI,JG_NAME); //将上次停机未判完的记录重判
    mysql_real_query(mysql,sql,strlen(sql));
    while(!Recv_SIGTERM || running_cnt>0)
    {
        // 1.
        while( (did=waitpid(-1,NULL,WNOHANG))>0 ) // 主动回收僵尸进程. WNOHANG不等待,若无僵尸进程立马返回0
        {
            running_cnt--;
            printf("Recycled process %d\n",did);
        }
        // 2.
        if(running_cnt>=max_running) // 被动(阻塞)回收子进程. 已达到最大并行判题数,只能等待任意判题进程结束
        {
            did=waitpid(-1,NULL,0);
            running_cnt--;
            printf("Recycled process %d\n",did);
        }
        // 3.
        if(Recv_SIGTERM) // 已终止，不再获取新任务
            continue;
        sid=get_a_waiting_solution(); // 获取1个waiting solution
        if(sid==-1)
        {
            sleep(1); //当前无题可判，休息1秒
            continue;
        }
        // 4.
        if( (pid=fork()) > 0 ) //父进程，判题数+1
            running_cnt++;
        else if(pid == 0)  //子进程，进行一次判题
        {
            sprintf(sid_str,"%d",sid);
            if( 0 > execl("./judge","",db_host,db_port,db_user,db_pass,db_name,sid_str,JG_DATA_DIR,(char*)NULL) )
                perror("Polling execl error:");
            exit(0);  //结束子进程
        }
        else //创建子进程出错
        {
            printf("Error: fork error!\n");
            exit(1);
        }
    }
}

int main (int argc, char* argv[])
{
    signal(SIGTERM, stop_polling); // 监听信号

    if(argc!=8+1){
        printf("Polling Error: argv error!\n%d\n",argc);
        exit(1);
    }
    db_host=argv[1];
    db_port=argv[2];
    db_user=argv[3];
    db_pass=argv[4];
    db_name=argv[5];
    max_running=atoi(argv[6]);
    JG_DATA_DIR=argv[7];
    JG_NAME=argv[8];

    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql_options(mysql,MYSQL_SET_CHARSET_NAME,"utf8mb4");//判题机名称可能有中文，故设置utf8mb4
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0);
    if(!mysql){
        printf("Polling Error: Can't connect to database!\n");
        exit(3);
    }

    polling();
    mysql_close(mysql);
    printf("[Stop Polling]: Good Bye!\n");
    return 0;
}
