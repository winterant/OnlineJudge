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

        if(this->language>1){    //非C/C++双倍资源
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
            p+=sprintf(p,"\' where id=%d;",this->id);
            mysql_real_query(mysql,new_sql,strlen(new_sql));
            delete new_sql;
        }
    }
}solution;


int file_size(const char* filename)//文件大小
{
    struct stat statbuf;
    stat(filename,&statbuf);
    return statbuf.st_size;
}

char *read_file(const char *filename)//从文件读取内容，返回字符串指针
{
    FILE *fp=fopen(filename,"r");
    if(fp==NULL) return NULL; //文件打开失败
    char ch, *p, *str = new char[file_size(filename)+3];
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

bool is_whitespace(char c) {
    return c == ' ' || c == '\t' || c == '\n' || c == '\r';
}
int compare_file(const char* fname1,const char *fname2) //比较两文件是否一致
{
    bool ok1,ok2;
    int rear1, rear2, result=OJ_AC;  //顺利的话，Accepted
    static char buf1[BUFFER_SIZE], buf2[BUFFER_SIZE];
    FILE *fp1=fopen(fname1,"r"), *fp2=fopen(fname2,"r");
    while(ok1=(fgets(buf1,BUFFER_SIZE,fp1)!=NULL), ok2=(fgets(buf2,BUFFER_SIZE,fp2)!=NULL), ok1&&ok2)
    {
        rear1=strlen(buf1)-1, rear2=strlen(buf2)-1;
        while(rear1>=0 && is_whitespace(buf1[rear1]))rear1--; //将rear1指向可见字符的最后一个
        while(rear2>=0 && is_whitespace(buf2[rear2]))rear2--;
        if(rear1!=rear2||strncmp(buf1,buf2,rear1+1)!=0) //文本不一致，wrong answer
        {
            result=OJ_WA;
            break;
        }
        else if(strcmp(buf1+rear1+1,buf2+rear2+1)!=0) //文本一致，但末尾空白字符不一致
        {
            result=OJ_PE;
            break;
        }
    }
    //没读完的文件内容含有非空白符，则用户wrong answer
    while( result==OJ_AC && (ok1||fgets(buf1,BUFFER_SIZE,fp1)!=NULL) )
    {
        ok1=false;
        for(char *ch=buf1;*ch;ch++)
            if(!is_whitespace(*ch))
                result=OJ_WA;
    }
    while( result==OJ_AC && (ok2||fgets(buf2,BUFFER_SIZE,fp2)!=NULL) )
    {
        ok2=false;
        for(char *ch=buf2;*ch;ch++)
            if(!is_whitespace(*ch))
                result=OJ_WA;
    }
    ok1=(fgets(buf1,BUFFER_SIZE,fp1)!=NULL);
    ok2=(fgets(buf2,BUFFER_SIZE,fp2)!=NULL);
    if(!(ok1&&ok2) && result==OJ_PE && rear1==rear2 && strncmp(buf1,buf2,rear1+1)==0) //最后一行空白字符不匹配不认为PE
        result=OJ_AC;
    fclose(fp1);
    fclose(fp2);
    return result;
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



int compile()
{
    if(solution.language==3)//python不需要编译
        return 0;
    //编译用户提交的代码
    const char *CP_C[]  ={"gcc","Main.c",  "-o","Main","-Wall","-lm","--static","-std=c99",  "-fmax-errors=5","-DONLINE_JUDGE","-O2",NULL};
    const char *CP_CPP[]={"g++","Main.cpp","-o","Main","-Wall","-lm","--static","-std=c++11","-fmax-errors=5","-DONLINE_JUDGE","-O2","-fno-asm", NULL};
    const char *CP_JAVA[]={"javac","-J-Xms64m","-J-Xmx128m","-encoding","UTF-8","Main.java",NULL};
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
    if(solution.language!=2) //memory limit，Java可以在执行命令中限制
    {
        LIM.rlim_max=LIM.rlim_cur = solution.memory_limit*(1<<20) + (1<<30); //Byte + 额外1GB
        setrlimit(RLIMIT_AS, &LIM); //memory limit;
    }

    //程序可创建的文件最大长度
    LIM.rlim_max=LIM.rlim_cur = 64<<20;
    setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 64MB

    //程序可创建的最大进程数;
    LIM.rlim_cur = LIM.rlim_max = solution.language>1 ? 200 : 1; // java,python扩大
    setrlimit(RLIMIT_NPROC, &LIM);

    //程序所使用的的堆栈最大空间
    LIM.rlim_cur = LIM.rlim_max = 128<<20;  //128MB
    setrlimit(RLIMIT_STACK, &LIM);

    //time limit
    LIM.rlim_max=LIM.rlim_cur = solution.time_limit/1000+1; //S,增加1秒额外损耗
    setrlimit(RLIMIT_CPU, &LIM);  // cpu time limit
    alarm(0);
    alarm((int)LIM.rlim_cur); //定时自杀

    switch(solution.language)
    {
        case 0: //c
        case 1: //c++
            chroot("./");
            execl("./Main", "./Main", (char *) NULL);
            break;
        case 2: //java
            char java_xmx[16];
            sprintf(java_xmx, "-Xmx%.fM", solution.memory_limit);
            execl("/usr/bin/java", "/usr/bin/java", java_xmx,
            				"-Djava.security.manager",
            				"-Djava.security.policy=../../java.policy", "Main", (char *) NULL);
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
    LIM.rlim_max=LIM.rlim_cur = 64<<20;
    setrlimit(RLIMIT_FSIZE, &LIM); //file size limit; 64MB

    //程序可创建的最大进程数;
    LIM.rlim_cur = LIM.rlim_max = 1;
    setrlimit(RLIMIT_NPROC, &LIM);

    //程序所使用的的堆栈最大空间
    LIM.rlim_cur = LIM.rlim_max = 256<<20;  //256MB
    setrlimit(RLIMIT_STACK, &LIM);

    int ret = system_cmd("./spj %s %s %s",in,out,user_out);
    if(ret==0) return OJ_AC;
    return OJ_WA;
}

//监视子进程running
int watch_running(int child_pid, char *test_name, int max_out_size)
{
    int status=0, result=OJ_TC; //初始result=测试通过
    struct rusage ruse;    //保存用户子进程的内存时间等
    float memory_MB=0;   //本次内存消耗
    while(1)
    {
        wait4(child_pid, &status, __WALL, &ruse); //跟踪子进程，子进程可能并未结束

        //内存使用情况
        if(solution.language==2)
            memory_MB = max(memory_MB, (ruse.ru_minflt * getpagesize())*1.0/(1<<20) ); //MB  java
        else
            memory_MB = max(memory_MB, get_proc_memory(child_pid)*1.0/(1<<10) ); //MB   c/c++,python

        if(memory_MB > solution.memory_limit){
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);//杀死子进程，停止执行
            result = OJ_ML; break; //memory limit exceeded
        }

        if(file_size("error.out")>0) {
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            if(solution.language==2 && strstr(read_file("error.out"),"OutOfMemoryError")!=NULL)
                result=OJ_ML;  //java超内存而被捕获异常OutOfMemoryError
            else result = OJ_RE;
            break; //运行错误
        }

        if (!solution.spj && file_size("user.out") > max_out_size ){
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            result = OJ_OL; break; //输出超限
        }

        if(WIFEXITED(status)) break; //仅代表子进程正常运行完成，不排除时间超限

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
            printf("[son-process exit]: runtime error! exit code = %d\n",exit_code);
            if(exit_code==11){
                char error[128];
                sprintf(error,"[ERROR] Illegal segment error (invalid memory reference)\n");
                write_file(error,"error.out","a+");
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
            printf("[son-process signal]: runtime error! signal value = %d\n",sig);
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            break;
        }

        //检查系统调用
		int sysCall = ptrace(PTRACE_PEEKUSER, child_pid, ORIG_RAX<<3, NULL);
		if(!allow_sys_call[sysCall]) //没有被许可的系统调用
		{
            result = OJ_RE;
            char error[128];
            sprintf(error,"[ERROR] A Not allowed system call: call id = %d, Please remove system function from your code!\n",sysCall);
            write_file(error,"error.out","a+");
            ptrace(PTRACE_KILL, child_pid, NULL, NULL);
            break;
		}

        ptrace(PTRACE_SYSCALL, child_pid, NULL, NULL); //唤醒暂停的子进程，继续执行
    }
    int used_time = (ruse.ru_utime.tv_sec*1000+ruse.ru_utime.tv_usec/1000); //用户态时间
                  + (ruse.ru_stime.tv_sec*1000+ruse.ru_stime.tv_usec/1000); //内核时间
    if(result!=OJ_TL && used_time>solution.time_limit)
        result = OJ_TL; //超时
    printf("%s | used time:   %5dMS, limit is %dMS\n", test_name, used_time, solution.time_limit);
    printf("%s | used memory: %5.2fMB, limit is %.2fMB\n", test_name, memory_MB, solution.memory_limit);
    solution.time   = max(solution.time,   min(solution.time_limit,   used_time) );
    solution.memory = max(solution.memory, min(solution.memory_limit, memory_MB) );
    return result;
}

//运行可执行文件
int judge(char *data_dir, char *spj_path)
{
    DIR *dir=opendir(data_dir);  //数据文件夹
    dirent *dirfile;
    if(dir==NULL){
        char error[128]="Missing test data, please contact the administrator to add test data!";
        write_file(error,"error.out","a+");
        return OJ_SE; //system error 缺少测试数据
    }

    if(solution.spj) //特判
    {
        if(access(spj_path,F_OK)==-1) //spj不存在
        {
            char error[] = "[ERROR] spj was not compiled successfully or spj.cpp does not exist! Please contact the administrator to resolve\n";
            write_file(error,"error.out","a+");
            return OJ_SE; //系统错误
        }
        system_cmd("/bin/cp %s ./spj",spj_path);
    }

    bool is_acm = (strcmp(solution.judge_type,"acm")==0);
    int test_count=0,ac_count=0, oi_result=OJ_AC;
    while((dirfile=readdir(dir))!=NULL)
    {
        char *test_name = isInFile(dirfile->d_name);
        if(test_name==NULL)continue; //不是输入数据，跳过
        system_cmd("/bin/cp %s/%s.in  ./data.in",data_dir,test_name); //复制输入数据到当前目录
        char *data_out_path = get_data_out_path(data_dir,test_name);  //输出文件路径
        test_count++;
        int pid=fork();
        if(pid==0)//child
        {
            printf("%s | running on test %d  ===================  %s.in(%dB)=>%s.out(%dB)\n",
            test_name, test_count,test_name,file_size("data.in"),test_name,file_size(data_out_path));
            running();
            exit(0);
        }
        else if(pid>0)
        {
            int result = watch_running(pid, test_name, file_size(data_out_path)*2+1024);
            if(result == OJ_TC)  //运行完成，需要判断用户的答案是否正确
            {
                if(solution.spj)  //special judge
                    result = running_spj("data.in",data_out_path,"user.out");
                else  //比较文件
                    result = compare_file(data_out_path,"user.out");  //非spj直接比较文件
            }
            printf("%s | judge result: %d\n\n",test_name,result);
            if(result==OJ_AC)ac_count++;
            if(is_acm && result!=OJ_AC)   //acm规则遇到WA直接返回，判题结束
                return result;
            if(!is_acm && result!=OJ_AC)   //oi规则遇到错误记下来
                oi_result=result;
        }
        else return OJ_SE;  //system error
    }
    if(test_count==0){
        char error[128]="Missing input file of test data, please contact the administrator to add test data!";
        write_file(error,"error.out","a+");
        return OJ_SE; //system error 缺少测试数据
    }
    solution.pass_rate = ac_count*1.0/test_count;
    if(is_acm)return OJ_AC;  //ACM规则走到这说明AC了，后面是oi
    return oi_result; //oi规则结果
}

int main (int argc, char* argv[])
{
    // 1. 读取参数
    if(argc!=7+1){
        printf("Judge arg number error!\n%d\n",argc);
        exit(1);
    }
    char *db_host=argv[1];
    char *db_port=argv[2];
    char *db_user=argv[3];
    char *db_pass=argv[4];
    char *db_name=argv[5];
    char *sid    =argv[6]; //solution id
    char *JG_DATA_DIR=argv[7];

    // 2. 连接数据库
    mysql = mysql_init(NULL);   //初始化数据库连接变量
    mysql_options(mysql,MYSQL_SET_CHARSET_NAME,"utf8mb4");
    mysql = mysql_real_connect(mysql,db_host,db_user,db_pass,db_name,atoi(db_port),NULL,0); //连接
    if(!mysql){
        printf("Judge Error: Can't connect to database!\n\n");
        exit(1);
    }

    // 3. 创建临时文件夹并进入
    if(access("../run",F_OK)==-1)
        mkdir("../run",0777);
    chdir("../run"); //进入工作目录
    mkdir(sid,0777);
    chdir(sid); //进入sid临时文件夹

    // 4.读取+编译+判题
    solution.load_solution(atoi(sid));   //从数据库读取提交记录
    write_file(solution.code,LANG[solution.language],"w"); //创建代码文件
    solution.update_result(OJ_CI); //update to compiling

    int CP_result=compile();
    if(CP_result==-1)//系统错误，正常情况下没有
    {
        printf("solution id: %s, compiling: System Error on fork();\n",sid);
        solution.result=OJ_SE;
    }
    else if(CP_result>0) //编译错误
    {
        printf("solution id: %s, compiling: Compile Error!\n",sid);
        solution.result=OJ_CE;
        solution.error_info = read_file("ce.txt");//将编译信息读到solution结构体变量
    }
    else    //编译成功，运行
    {
        printf("solution id: %s, Compiling successfully! start running\n",sid);
        solution.update_result(OJ_RI); //update to running
        char data_dir[256], spj_path[256];
        sprintf(data_dir,"%s/%d/test",JG_DATA_DIR,solution.problem_id); //测试数据所在文件夹
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
        solution.result = judge(data_dir, spj_path);
        solution.error_info = read_file("error.out");
    }

    // 5. 判题结果写回数据库
    solution.update_solution();    // update all of data

    // 6. 关闭数据库+删除临时文件夹
    mysql_close(mysql);
    system_cmd("now_pwd=`pwd` && cd .. && rm -rf ${now_pwd}"); //删除该记录所用的临时文件夹
    return 0;
}
