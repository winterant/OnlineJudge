<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Illuminate\Support\Facades\DB;
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
    private function init_user_admin($admin_name = 'admin')
    {
        echo "--------------------- init_user_admin -----------------------" . PHP_EOL;
        //============= 尝试创建管理员账号
        $user = User::where('username', $admin_name)->first();
        if (!$user) {
            $user = new User(['username' => $admin_name, 'password' => 'adminadmin']);
            $user->save();
            echo "--------------- Created first user ---------" . PHP_EOL;
            echo "Username:" . $user->username . PHP_EOL;
            echo "Password:" . $user->password . PHP_EOL;
            echo "Please reset your password as soon as possible!" . PHP_EOL;
            echo "--------------------------------------------" . PHP_EOL;
        }
        //============= 为admin赋予最高权限
        $user->givePermissionTo('admin');
        echo "Gave permision [admin] to user [admin]" . PHP_EOL;
        // 临时旧版本权限表赋权
        DB::table('privileges')->insert(['user_id'=>$user->id,'authority'=>'admin']);
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
}
