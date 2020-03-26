#include<stdio.h>
#include<stdlib.h>
#include<stdarg.h>
#include<string.h>
#include<time.h>
#include<unistd.h>
#include<dirent.h>
#include<sys/stat.h>
#include<sys/signal.h>
#include<sys/types.h>
#include<sys/wait.h>
#include<sys/resource.h>
#include<sys/ptrace.h>
#include<sys/user.h>
#include<sys/reg.h>
#include<sys/syscall.h>
#include<mysql/mysql.h>

#define max(a,b) ((a)>(b) ? (a) : (b))
#define min(a,b) ((a)<(b) ? (a) : (b))



#define OJ_WT  0    //waiting
#define OJ_QI  1    //queueing
#define OJ_CI  2    //compiling
#define OJ_RI  3    //running
#define OJ_AC  4    //accepted
#define OJ_PE  5    //presentation error
#define OJ_WA  6    //wrong answer
#define OJ_TL  7    //time limit exceeded
#define OJ_ML  8    //memory limit exceeded
#define OJ_OL  9    //output limit exceeded
#define OJ_RE 10    //runtime error
#define OJ_CE 11    //compile error
#define OJ_TC 12    //test completed
#define OJ_SK 13    //skipped
#define OJ_SE 14    //system error

#define BUFFER_SIZE 5<<10    //about 5KB

#define COMPILE_TIME 10     //10s, compile time limit
#define COMPILE_FSIZE (10<<20)  //10MB,compile file size limit
#define COMPILE_MEM (512<<20)  //512MB,compile memory

const char *LANG[]={"Main.c","Main.cpp","Main.java","Main.py"}; //判题文件名

//x64
//允许用户的系统调用c/c++
int LANG_C[] = {0,1,3,4,5,8,9,11,12,20,21,59,63,89,99,158,202,231,240,272,273,275,511,
        SYS_time, SYS_read, SYS_uname, SYS_write,/*SYS_open,*/
		SYS_close, SYS_access, SYS_brk, SYS_munmap, SYS_mprotect,
		SYS_mmap, SYS_fstat, SYS_set_thread_area, 252, SYS_arch_prctl, EOF};

//java
int LANG_JAVA[] = { 0,39,157,257,302,3,4,5,9,10,11,12,13,14,21,56,59,89,97,104,158,202,218,231,273,257,
		61, 22, 6, 33, 8, 13, 16, 111, 110, 39, 79, SYS_fcntl,
		SYS_getdents64, SYS_getrlimit, SYS_rt_sigprocmask, SYS_futex, SYS_read,
		SYS_mmap, SYS_stat, SYS_open, SYS_close, SYS_execve, SYS_access,
		SYS_brk, SYS_readlink, SYS_munmap, SYS_close, SYS_uname, SYS_clone,
		SYS_uname, SYS_mprotect, SYS_rt_sigaction, SYS_getrlimit, SYS_fstat,
		SYS_getuid, SYS_getgid, SYS_geteuid, SYS_getegid, SYS_set_thread_area,
		SYS_set_tid_address, SYS_set_robust_list, SYS_exit_group, 158, EOF};
//python
int LANG_PY[] = {3,4,5,6,8,9,10,11,12,13,14,16,21,32,59,72,78,79,89,97,99,102,104,107,108,
        131,158,217,218,228,231,272,273,318,39,99,302,99,32,72,131,202,257,41, 42, 146,
        SYS_mremap, 158, 117, 60, 39, 102, 191,
		SYS_access, SYS_arch_prctl, SYS_brk, SYS_close, SYS_execve,
		SYS_exit_group, SYS_fcntl, SYS_fstat, SYS_futex, SYS_getcwd,
		SYS_getdents, SYS_getegid, SYS_geteuid, SYS_getgid, SYS_getrlimit,
		SYS_getuid, SYS_ioctl, SYS_lseek, SYS_lstat, SYS_mmap, SYS_mprotect,
		SYS_munmap, SYS_open, SYS_read, SYS_readlink, SYS_rt_sigaction,
		SYS_rt_sigprocmask, SYS_set_robust_list, SYS_set_tid_address, SYS_stat,
		SYS_write, SYS_statfs, EOF};

bool allow_sys_call[512]={0}; //系统调用标记

char *db_host;
char *db_port;
char *db_user;
char *db_pass;
char *db_name;

MYSQL *mysql;    //数据库连接对象
MYSQL_RES *mysql_res;   //sql查询结果
MYSQL_ROW mysql_row;    //sql查询到的单行数据
char sql[BUFFER_SIZE];   //暂存sql语句


//结构体，一条提交记录
struct Solution{
    int id;
    char *judge_type;
    int problem_id;
    int spj;
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
        sprintf(sql,"select `judge_type`,`problem_id`,`spj`,`time_limit`,`memory_limit`,`language`,`code` from solutions A inner join problems B on A.problem_id=B.id where A.id=%d",sid);
        if(mysql_real_query(mysql,sql,strlen(sql))!=0){
            printf("select failed!\n");
            exit(1);
        }
        mysql_res=mysql_store_result(mysql); //保存查询结果
        mysql_row=mysql_fetch_row(mysql_res); //读取
        this->id=sid;
        this->judge_type  =mysql_row[0];
        this->problem_id  =atoi(mysql_row[1]);
        this->spj         =atoi(mysql_row[2]);
        this->time_limit  =atoi(mysql_row[3]);
        this->memory_limit=atof(mysql_row[4]);
        this->language    =atoi(mysql_row[5]);
        this->code        =mysql_row[6];

        mysql_free_result(mysql_res); //必须释放结果集

        if(this->language>1){
            this->time_limit*=2;
            this->memory_limit*=2;
        }

    }

    void update_result(int result)  //数据库，只更新result
    {
        this->result=result;
        sprintf(sql,"UPDATE solutions SET result=%d WHERE id=%d",this->result,this->id);
        mysql_real_query(mysql,sql,strlen(sql));
    }

    void update_solution()  //数据库，更新solution
    {
        sprintf(sql,"UPDATE solutions SET result=%d,time=%d,memory=%f,pass_rate=%f,judge_time=now(),error_info=NULL WHERE id=%d",
            this->result,this->time,this->memory,this->pass_rate,this->id); //更新
        mysql_real_query(mysql,sql,strlen(sql));
        if(this->error_info!=NULL){    //更新出错信息
            char *new_sql = new char[2*strlen(this->error_info)+64];
            char *p=new_sql;
            p+=sprintf(p,"UPDATE solutions SET error_info=\'");
            p+=mysql_real_escape_string(mysql,p,this->error_info,strlen(this->error_info));
            p+=sprintf(p,"\' where id=%d",this->id);
            mysql_real_query(mysql,new_sql,strlen(new_sql));
            delete new_sql;
        }
    }
}solution;


int get_file_size(const char *filename)//获取文件内容长度
{
    FILE *fp=fopen(filename,"r");
    if(fp==NULL)return -1; //文件打开失败
    fseek(fp,0L,SEEK_END);
    int size=ftell(fp);  //获得内容长度
    fclose(fp);
    return size;
}

char *read_file(const char *filename)//从文件读取内容，返回字符串指针
{
    int file_size=get_file_size(filename);
    FILE *fp=fopen(filename,"r");
    if(fp==NULL) return NULL; //文件打开失败
    char ch, *p, *str = new char[file_size+3];
    for (p=str;(ch=fgetc(fp))!=EOF;*p++=ch);
    *p='\0';
    fclose(fp);
    return str;
}

void write_file(const char *str, const char *filename,const char* mode)//将字符串写入文件
{
    FILE *fp=fopen(filename,mode);
    fprintf(fp,"%s",str);
    fclose(fp);
}

char* isInFile(const char fname[])  //检查文件名后缀是否为.in
{
	int len = strlen(fname);
	if (len > 3 && strcmp(fname + len - 3, ".in") == 0)
	{
	    char *name=new char[len];
	    strncpy(name,fname,len-3);
	    name[len-3]='\0';
	    return name;//返回文件名
	}
	return NULL;
}

char* get_data_out_path(const char* data_dir,const char* test_name) //获取测试答案的路径
{
    char *path = new char[256];
    sprintf(path,"%s/%s.out",data_dir,test_name);
    return path;
}

int compare_file(const char* fname1,const char *fname2) //比较两文件是否一致
{
    char *text1 = read_file(fname1);
    char *text2 = read_file(fname2);
    char *text1_end = text1 + strlen(text1)-1;
    while(text1!=text1_end && *text1_end == '\n')*text1_end--='\0'; //忽略末尾换行
    char *text2_end = text2 + strlen(text2)-1;
    while(text2!=text2_end && *text2_end == '\n')*text2_end--='\0'; //忽略末尾换行
    if(strcmp(text1,text2)==0)
        return OJ_AC;
    return OJ_WA;
}

int get_proc_memory(int pid)//读取进程pid的内存使用情况
{
	int memory = 0;
	char buf[64], mark[]="VmPeak:";
	sprintf(buf, "/proc/%d/status", pid);
	FILE *fp = fopen(buf, "r");
	int mark_len = strlen(mark);
	while (fp && fgets(buf, 62, fp)) {
		buf[strlen(buf) - 1] = 0;
		if(strncmp(buf, mark, mark_len) == 0){
			sscanf(buf + mark_len + 1, "%d", &memory);
			break;
        }
	}
	if(fp) fclose(fp);
	return memory;  //Byte
}


int system_cmd(const char *fmt, ...) //执行一条linux命令
{
	char cmd[BUFFER_SIZE];
	va_list ap;
	va_start(ap, fmt);
	vsprintf(cmd, fmt, ap);
	int ret = system(cmd);
	va_end(ap);
	return ret;
}


//编译用户提交的代码
const char *CP_C[]  ={"gcc","Main.c",  "-o","Main","-Wall","-lm","--static","-std=c99",  "-fmax-errors=5","-DONLINE_JUDGE","-O2",NULL};
const char *CP_CPP[]={"g++","Main.cpp","-o","Main","-Wall","-lm","--static","-std=c++11","-fmax-errors=5","-DONLINE_JUDGE","-O2","-fno-asm", NULL};
const char *CP_JAVA[]={"javac","-J-Xms64m","-J-Xmx128m","-encoding","UTF-8","Main.java",NULL};
int compile()
{
    if(solution.language==3)//python不需要编译
        return 0;
    int pid;

    if( (pid=fork()) == 0 ) //子进程编译
    {
        struct rlimit LIM;
        LIM.rlim_max=LIM.rlim_cur=COMPILE_TIME;
        setrlimit(RLIMIT_CPU, &LIM);  // cpu time limit; 10s
        LIM.rlim_max=LIM.rlim_cur=COMPILE_FSIZE;
        setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 10MB
        LIM.rlim_max=LIM.rlim_cur= solution.language>1 ? COMPILE_MEM<<2 : COMPILE_MEM; //java,python要扩大
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

    return -1;  //-1:system error
}

//运行一次用户程序，产生用户答案user.out
void running()
{
    nice(19); //优先级-20~19，19最低
    freopen("data.in", "r", stdin);
    freopen("user.out", "w", stdout);
    freopen("error.out", "a+", stderr);
    ptrace(PTRACE_TRACEME, 0, NULL, NULL); //让父进程跟踪自己

    struct rlimit LIM;
    //time limit
    LIM.rlim_max=LIM.rlim_cur = solution.time_limit/1000.0+1; //S,增加1秒额外损耗
    setrlimit(RLIMIT_CPU, &LIM);  // cpu time limit
    alarm(0);
    alarm((int)LIM.rlim_cur);

    //memory limit
    LIM.rlim_max=LIM.rlim_cur = solution.memory_limit*(1<<20)*2; //Byte, *200%
    setrlimit(RLIMIT_AS, &LIM); //memory limit;

    //程序可创建的文件最大长度
    LIM.rlim_max=LIM.rlim_cur = 16<<20;
    setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 16MB

    //程序可创建的最大进程数;
    LIM.rlim_cur = LIM.rlim_max = solution.language>1 ? 200 : 1; // java,python扩大
    setrlimit(RLIMIT_NPROC, &LIM);

    //程序所使用的的堆栈最大空间
    LIM.rlim_cur = LIM.rlim_max = 256<<20;  //256MB
    setrlimit(RLIMIT_STACK, &LIM);

    switch(solution.language)
    {
        case 0: //c
        case 1: //c++
            execl("./Main", "./Main", (char *) NULL);
            break;
        case 2: //java
            char java_xmx[16];
            sprintf(java_xmx, "-Xmx%.fM", solution.memory_limit);
            execl("/usr/bin/java", "/usr/bin/java", java_xmx,
            				"-Djava.security.manager",
            				"-Djava.security.policy=./java.policy", "Main", (char *) NULL);
            break;
        case 3: //python 3.6
            execl("/usr/bin/python3", "/usr/bin/python3", "Main.py", (char *) NULL);
            break;
    }
    fflush(stderr);
}

//运行special judge
int running_spj(const char *in,const char *out,const char *user_out)
{
    struct rlimit LIM;
    //time limit
    LIM.rlim_max=LIM.rlim_cur = 60; //60S
    setrlimit(RLIMIT_CPU, &LIM);  // cpu time limit
    alarm(0);
    alarm((int)LIM.rlim_cur);

    //memory limit
    LIM.rlim_max=LIM.rlim_cur = 1024<<20; //1024MB
    setrlimit(RLIMIT_AS, &LIM);

    //程序可创建的文件最大长度
    LIM.rlim_max=LIM.rlim_cur = 16<<20;
    setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 16MB

    //程序可创建的最大进程数;
    LIM.rlim_cur = LIM.rlim_max = solution.language>1 ? 200 : 1; // java,python扩大
    setrlimit(RLIMIT_NPROC, &LIM);

    //程序所使用的的堆栈最大空间
    LIM.rlim_cur = LIM.rlim_max = 256<<20;  //256MB
    setrlimit(RLIMIT_STACK, &LIM);

    int ret = system_cmd("./spj %s %s %s",in,out,user_out);
    if(ret==0) return OJ_AC;
    return OJ_WA;
}

//监视子进程running
int watch_running(int child_pid, float &memory_MB, int &time_MS, int max_out_size)
{
    int status=0, result=OJ_TC; //初始result=测试通过
    struct rusage ruse;    //保存用户子进程的内存时间等
    while(1)
    {
        wait4(child_pid, &status, __WALL, &ruse); //跟踪子进程，子进程可能并未结束

        //内存使用情况
        if(solution.language==2) memory_MB = max(memory_MB, (ruse.ru_minflt * getpagesize())*1.0/(1<<20) ); //MB  java
        else  memory_MB = max(memory_MB, get_proc_memory(child_pid)*1.0/(1<<10) ); //MB   c/c++,python
        if(memory_MB > solution.memory_limit) //内存超限
        {
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);//杀死子进程，停止执行
            result = OJ_ML; break; //memory limit exceeded
        }

        if(WIFEXITED(status)) break; //仅代表子进程正常运行完成

        if(get_file_size("error.out")>0) {
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            result = OJ_RE; break; //运行错误
        }

        if (!solution.spj && get_file_size("user.out") > max_out_size ){
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            result = OJ_OL; break; //输出超限
        }

        int exit_code = WEXITSTATUS(status);  //子进程退出码, 注意子进程可能并未真正结束，只是一个断点
		if (!((solution.language>1&&exit_code==17) || exit_code==0 || exit_code==133 || exit_code==5) ){
            switch (exit_code) {
                case SIGCHLD : case SIGALRM :
                    alarm(0);
                case SIGKILL : case SIGXCPU :
                    result = OJ_TL; break;  //超时
                case SIGXFSZ :
                    result = OJ_OL; break;  //输出超限
                default :
                    result = OJ_RE;     //默认运行错误
            }
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            break;
		}
        if(WIFSIGNALED(status)) //子进程异常终止
        {
            int sig = WTERMSIG(status); //信号
            switch (sig) {
                case SIGCHLD : case SIGALRM :
                    alarm(0);
                case SIGKILL : case SIGXCPU :
                    result = OJ_TL; break;  //超时
                case SIGXFSZ :
                    result = OJ_OL;  break;  //输出超限
                default :
                    result = OJ_RE;     //默认运行错误
            }
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            break;
        }

        //检查系统调用
		int sysCall = ptrace(PTRACE_PEEKUSER, child_pid, ORIG_RAX<<3, NULL);
		if(!allow_sys_call[sysCall]) //没有被许可的系统调用
		{
            result = OJ_RE;
            char error[64];
            sprintf(error,"[ERROR] A Not allowed system call: call id = %d\n",sysCall);
            write_file(error,"error.out","a+");
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            break;
		}

        ptrace(PTRACE_SYSCALL, child_pid, NULL, NULL); //唤醒暂停的子进程，继续执行
    }
    int used_time = (ruse.ru_utime.tv_sec * 1000 + ruse.ru_utime.tv_usec / 1000); //用户态时间
    used_time += (ruse.ru_stime.tv_sec * 1000 + ruse.ru_stime.tv_usec / 1000); //内核时间
    time_MS = max(time_MS,used_time);
    if(used_time>solution.time_limit)
        result = OJ_TL; //超时
    printf("running used time: %dMS,  limit is %dMS\n",time_MS,solution.time_limit);
    printf("running used memory: %f MB, limit is %fMB\n",memory_MB,solution.memory_limit);
    if(result == OJ_TL)
        solution.time = solution.time_limit;
    return result;
}


//运行可执行文件
int judge(char *data_dir, char *spj_path)
{
    DIR *dir=opendir(data_dir);  //数据文件夹
    dirent *dirfile;
    if(dir==NULL){
        printf("problem %d doesn't have test data!\n",solution.problem_id);
        return OJ_AC; //accepted
    }

    if(solution.spj) //特判
    {
        if(access(spj_path,F_OK)==-1) //spj不存在
        {
            char error[] = "[ERROR] spj was not compiled successfully or spj.cpp does not exist!\n";
            write_file(error,"error.out","a+");
            return OJ_SE; //系统错误
        }
        system_cmd("/bin/cp %s ./spj",spj_path);
    }

    int test_count=0,ac_count=0;
    while((dirfile=readdir(dir))!=NULL)
    {
        char *test_name = isInFile(dirfile->d_name);
        if(test_name==NULL)continue; //不是输入数据，跳过
        system_cmd("/bin/cp %s/%s.in  ./data.in",data_dir,test_name); //复制输入数据到当前目录
        test_count++;
        int pid=fork();
        if(pid==0)//child
        {
            printf("running on test %d\n",test_count);
            running();
            exit(0);
        }
        else if(pid>0)
        {
            char *data_out_path = get_data_out_path(data_dir,test_name);
            int result = watch_running(pid, solution.memory, solution.time, get_file_size(data_out_path)*2+1024);
            if(result == OJ_TC)  //运行完成，需要判断用户的答案
            {
                if(solution.spj)  //special judge
                    result = running_spj("data.in",data_out_path,"user.out");
                else  //比较文件
                    result = compare_file(data_out_path,"user.out");  //非spj直接比较文件
            }
            printf("test %d result: %d\n",test_count,result);
            if(result==OJ_AC)ac_count++;
            if(strcmp(solution.judge_type,"acm")==0 && result!=OJ_AC) //acm遇到WA直接返回
                return result;
        }
        else return OJ_SE;  //system error
    }
    solution.pass_rate = ac_count*1.0/test_count;
    return OJ_AC; //accepted
}

int main (int argc, char* argv[])
{
    // 1. 读取参数
    if(argc!=7+1){
        printf("Judge arg number error!\n%d\n",argc);
        exit(1);
    }
    db_host=argv[1];
    db_port=argv[2];
    db_user=argv[3];
    db_pass=argv[4];
    db_name=argv[5];
    int sid=atoi(argv[6]); //solution id
    char *JG_DATA_DIR=argv[7];

    // 2. 连接数据库
    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0); //连接
    if(!mysql){
        printf("Judge Error: Can't connect to database!\n\n");
        exit(1);
    }

    // 3. 创建临时文件夹并进入
    if(access("../run",F_OK)==-1)
        mkdir("../run",0777);
    chdir("../run"); //进入工作目录
    mkdir(argv[6],0777);
    chdir(argv[6]); //进入sid临时文件夹

    // 4.读取+编译+判题
    solution.load_solution(sid);   //从数据库读取提交记录
    write_file(solution.code,LANG[solution.language],"w"); //创建代码文件
    solution.update_result(OJ_CI); //update to compiling

    int CP_result=compile();
    if(CP_result==-1)//系统错误，正常情况下没有
    {
        printf("solution id: %d, compiling: System Error on fork();\n",sid);
        solution.result=OJ_SE;
    }
    else if(CP_result>0) //编译错误
    {
        printf("solution id: %d, compiling: Compile Error!\n",sid);
        solution.result=OJ_CE;
        solution.error_info = read_file("ce.txt");//将编译信息读到solution结构体变量
    }
    else    //编译成功，运行
    {
        printf("solution id: %d, Compiling successfully! start running\n",sid);
        solution.update_result(OJ_RI); //update to running
        char data_dir[256], spj_path[256];
        sprintf(data_dir,"%s/%d/test",JG_DATA_DIR,solution.problem_id); //测试数据
        sprintf(spj_path,"%s/%d/spj/spj",JG_DATA_DIR,solution.problem_id); //特判程序spj的路径

        //标记允许的系统调用
        int *call_lang=NULL;
        switch(solution.language)
        {
            case 0: case 1: call_lang = LANG_C; break;
            case 2: call_lang = LANG_JAVA; break;
            case 3: call_lang = LANG_PY; break;
            default: call_lang = LANG_C;
        }
        for(int i=0;call_lang[i]!=EOF; i++)
            allow_sys_call[call_lang[i]]=true; //允许调用

        //开始判题
        system_cmd("rm -rf error.out");
        solution.result = judge(data_dir, spj_path);
        solution.error_info = read_file("error.out");
    }

    // 5. 判题结果写回数据库
    solution.update_solution();    // update all of data

    // 6. 关闭数据库+删除临时文件夹
    mysql_close(mysql);
    system_cmd("now_pwd='pwd' && cd .. && rm -rf ${now_pwd}"); //删除该记录所用的临时文件夹
    return 0;
}
