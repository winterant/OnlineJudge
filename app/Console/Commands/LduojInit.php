<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LduojInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lduoj:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'lduoj:init. Initialize neccessary data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "Run command 'lduoj:init'." . PHP_EOL;
        $this->init_permission();
        $this->init_user_admin();
        $this->init_contest_cate();
        $this->correct_contest_order();
        echo "Done!" . PHP_EOL;
    }

    // 初始化权限管理 spatie/laravel-permission
    private function init_permission()
    {
        echo "--------------------- init_permission -----------------------" . PHP_EOL;
        //============= 创建权限
        // foreach (config('auth.guards') as $guard_name => $v)
        foreach (config('init.permissions') as $name => $attr) {
            $permission = Permission::findOrCreate($name, $guard_name ?? null);
        }
        echo 'All Permissions: ';
        print_r(json_decode(json_encode(Permission::all()), true));

        //============= 创建预置角色，并分配预置权限
        if (Role::count() > 0) // 已有角色，则不打扰了
            return false;
        // foreach (config('auth.guards') as $guard_name => $v)
        foreach (config('init.roles') as $name => $permissions) {
            $role = Role::findOrCreate($name, $guard_name ?? null);
            $role->syncPermissions($permissions);
            echo "--------------- Role [{$name}] ---------------" . PHP_EOL;
            print_r(json_decode(json_encode($role), true));
        }
        return true;
    }

    // 初始化超级管理员用户
    private function init_user_admin($admin_name = 'admin', $admin_passwd = 'adminadmin')
    {
        echo "--------------------- init_user_admin -----------------------" . PHP_EOL;
        //============= 尝试创建管理员账号
        $user = User::where('username', $admin_name)->first();
        if (!$user) {
            $user = new User(['username' => $admin_name, 'password' => Hash::make($admin_passwd)]);
            $user->save();
            echo "--------------- Created first user ---------" . PHP_EOL;
            echo "Username:" . $admin_name . PHP_EOL;
            echo "Password:" . $admin_passwd . PHP_EOL;
            echo "Please reset your password as soon as possible!" . PHP_EOL;
            echo "--------------------------------------------" . PHP_EOL;
        }
        //============= 为admin赋予最高权限
        $user->givePermissionTo('admin');
        echo "Gave permision [admin] to user [admin]" . PHP_EOL;
        // 临时旧版本权限表赋权
        DB::table('privileges')->insert(['user_id' => $user->id, 'authority' => 'admin']);
        return true;
    }

    // 初始化竞赛类别
    private function init_contest_cate()
    {
        echo "--------------------- init_contest_cate -----------------------" . PHP_EOL;
        if (DB::table('contest_cate')->count() > 0) // 已有类别则不要创建
            return false;
        echo "Create some default contest categary..." . PHP_EOL;
        //一级默认类别
        $ids[] = DB::table('contest_cate')->insertGetId(['title' => '竞赛', 'description' => '程序设计竞赛']);
        $ids[] = DB::table('contest_cate')->insertGetId(['title' => '作业', 'description' => '日常作业']);
        //二级默认类别
        DB::table('contest_cate')->insert(['title' => 'C/C++', 'parent_id' => $ids[1]]);
        DB::table('contest_cate')->insert(['title' => '数据结构', 'parent_id' => $ids[1]]);
        DB::update("update contest_cate set `order`=`id`");
        return true;
    }

    /**
     * 竞赛order字段用于竞赛列表的顺序排序，每个类别的竞赛内部单独排序，不同类别之间order不通
     * 在每个类别内部，order的取值范围是[1,n]，n表示类别内部竞赛个数。
     * 前后台展示时，均采用降序排序，即新竞赛总是显示在最前面
     *
     * lduoj-v1.2中新增了该排序方法，对于老版本，将使用此函数进行矫正
     * 默认以id字段顺序重写order字段：1,2,3,...,n
     */
    private function correct_contest_order()
    {
        // 拿到所有的类别（一级/二级）
        $categories = DB::table('contest_cate')->orderByDesc('id')->pluck('id');
        // 每个类别内部单独处理order
        foreach ($categories as $cate_id) {
            $info = DB::table('contests')
                ->select([
                    DB::raw('count(*) as num_contests'),
                    DB::raw('count(distinct `order`) as num_distinct_order'),
                    DB::raw('max(`order`) as max_order')
                ])
                ->where('cate_id', $cate_id)
                ->orderByDesc('id')
                ->first();
            // 检查order字段是否已经全部处于[1,n]且无重复，否则重排
            if ($info->num_contests == $info->num_distinct_order && $info->num_contests == $info->max_order) {
                echo "[OK] Contests of category " . $cate_id . " have correct order." . PHP_EOL;
            } else {
                echo "[Wrong] Contests of category " . $cate_id . " have incorrect order. Correct them to 1,2,3,...," . $info->num_contests . PHP_EOL;
                // 对order字段赋值为(1,2,3,...,n)，update默认按主键升序依次赋值
                $updated = DB::table('contests')
                    ->join(DB::raw('(SELECT @row_num := 0) as row_num_table'), DB::raw('1'), DB::raw('1'))
                    ->where('cate_id', $cate_id)
                    ->update(['order' => DB::raw('(@row_num:=@row_num+1)')]);
            }
        }
    }
}
