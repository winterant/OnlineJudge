<?php

namespace App\Console\Commands;

use App\Http\Helpers\DBHelper;
use App\Jobs\Solution\CorrectSolutionsStatistics;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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

        // 初始化权限和管理员
        $this->init_permission();
        $this->init_user_admin();

        // 初始化竞赛类别，校正竞赛、竞赛类别顺序
        $this->init_contest_cate();
        $this->correct_contest_order();
        $this->correct_contest_cate_order();
        $this->correct_group_contest_order();

        // 清除重启后失效的cache
        Cache::forget('web:version');

        // 矫正过题数字段，延迟2分钟执行（待服务器稳定运行）
        dispatch(new CorrectSolutionsStatistics())->delay(120);

        echo "Command `php artisan lduoj:init` done!" . PHP_EOL;
    }

    /**
     * 新部署的Online Judge没有任何权限项，初始化必要的权限。
     * 如果版本迭代过程中，配置文件init/permissions新增了权限项，此处会自动在数据库中增加它们
     * 权限管理第三方包： spatie/laravel-permission
     */
    private function init_permission()
    {
        echo "--------------------- init_permission -----------------------" . PHP_EOL;
        //============= 创建权限
        // foreach (config('auth.guards') as $guard_name => $v)
        foreach (config('init.permissions') as $name => $attr) {
            $permission = Permission::findOrCreate($name, $guard_name ?? null);
        }
        // echo 'All Permissions: ';
        // print_r(json_decode(json_encode(Permission::all()), true));

        //============= 创建预置角色，并分配预置权限
        if (Role::count() >= count(config('init.roles'))) // 已有角色数量达到预置角色数，则不自动添加了
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

    /**
     * 新部署的Online Judge没有任何用户，生成一个用户，并设为超级管理员
     */
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
        // $user->givePermissionTo('admin');
        // echo "Gave permision [admin] to user [admin]" . PHP_EOL;
        $user->assignRole('admin');
        echo "Assign role [admin] to user [admin]" . PHP_EOL;
        // 遗弃的。旧版本权限表赋权
        // DB::table('privileges')->insert(['user_id' => $user->id, 'authority' => 'admin']);
        return true;
    }

    /**
     * 新部署的Online Judge没有任何竞赛类别，初始化几个必要的竞赛类别
     *  */
    private function init_contest_cate()
    {
        echo "--------------------- init_contest_cate -----------------------" . PHP_EOL;
        if (DB::table('contest_cate')->exists()) // 已有类别则不要创建
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
    private function correct_contest_order($categories = null)
    {
        if ($categories == null) {
            // 修正未分类竞赛为0类
            DB::table('contests')->where('cate_id', null)->update(['cate_id' => 0]);
            // 拿到所有的类别（一级/二级）
            $categories = DB::table('contest_cate')->pluck('id')->toArray();
            $categories[] = 0; // 未分类的竞赛也要排序
        }
        // 每个类别内部单独处理order
        foreach ($categories as $cate_id) {
            $info = DB::table('contests')
                ->select([
                    DB::raw('count(*) as num_contests'),
                    DB::raw('count(distinct `order`) as num_distinct_order'),
                    DB::raw('max(`order`) as max_order')
                ])
                ->where('cate_id', $cate_id)
                ->first();
            // 检查order字段是否已经全部处于[1,n]且无重复，否则重排
            if ($info->num_contests == $info->num_distinct_order && $info->num_contests == $info->max_order) {
                // echo "[Order is OK] Contests of category " . $cate_id . " have correct order." . PHP_EOL;
            } else {
                echo "[Wrong Order] Contests of category " . $cate_id . " have incorrect order. Correct them to 1,2,3,...," . $info->num_contests . PHP_EOL;
                // 对order字段赋值为(1,2,3,...,n)，update默认按主键升序依次赋值
                DBHelper::continue_order('contests', ['cate_id' => $cate_id]);
                // $updated = DB::table('contests')
                //     ->leftJoin(DB::raw('(SELECT @row_num := 0) as row_num_table'), DB::raw('1'), DB::raw('1'))
                //     ->where('cate_id', $cate_id)
                //     ->update(['order' => DB::raw('(@row_num:=@row_num+1)')]);
            }
        }
    }

    /**
     * 竞赛类别order字段用于类别的顺序排序，一级类别单独排序，内部二级类别之间单独排order
     * 在每个一级类别内部，order的取值范围是[1,n]，n表示一级类别内部二级类别个数。
     * 前后台展示时，均采用升序排序
     *
     * lduoj-v1.2中新增了该排序方法，对于老版本，将使用此函数进行矫正
     * 默认以id字段顺序重写order字段：1,2,3,...,n
     */
    private function correct_contest_cate_order()
    {
        // 拿到所有的类别（一级/二级）的父类别编号
        $parent_ids = DB::table('contest_cate')->select('parent_id')->distinct()->pluck('parent_id');

        // 每个大类内部排序。所有一级类别视为同一类
        foreach ($parent_ids as $parent_id) {
            $info = DB::table('contest_cate')
                ->select([
                    DB::raw('count(*) as count'),
                    DB::raw('count(distinct `order`) as num_distinct_order'),
                    DB::raw('max(`order`) as max_order')
                ])
                ->where('parent_id', $parent_id)
                ->first();
            if ($info->count == $info->num_distinct_order && $info->count == $info->max_order) {
                // echo "[Order is OK] Parent categories with `parent_id` " . $parent_id . " have correct order." . PHP_EOL;
            } else {
                echo "[Wrong Order] Parent categories with `parent_id` " . $parent_id . " have incorrect order. Correct them to 1,2,3,...," . $info->count . PHP_EOL;
                // 对order字段赋值为(1,2,3,...,n)，update默认按主键升序依次赋值
                DBHelper::continue_order('contest_cate', ['parent_id' => $parent_id]);
                // $updated = DB::table('contest_cate')
                //     ->leftJoin(DB::raw('(SELECT @row_num := 0) as row_num_table'), DB::raw('1'), DB::raw('1'))
                //     ->where('parent_id', $parent_id)
                //     ->update(['order' => DB::raw('(@row_num:=@row_num+1)')]);
            }
        }
    }


    /**
     * 竞赛order字段用于竞赛列表的顺序排序，每个群组中的竞赛单独排序，不同群组之间order不通
     * 在每个群组内部，order的取值范围是[1,n]，n表示群组竞赛个数。
     *
     * lduoj-v1.5中新增了该排序方法，对于老版本，将使用此函数进行矫正
     * 默认以id字段顺序重写order字段：1,2,3,...,n
     */
    private function correct_group_contest_order()
    {
        // 筛选出竞赛顺序混乱的那些群组
        $group_contests = DB::table('group_contests')
            ->select([
                'group_id',
                DB::raw('count(*) as num_contests'),
                DB::raw('count(distinct `order`) as num_distinct_order'),
                DB::raw('max(`order`) as max_order')
            ])
            ->groupBy('group_id')
            ->havingRaw('`num_distinct_order` != `num_contests`')
            ->orHavingRaw('`max_order` != `num_contests`')
            ->get();
        // 对竞赛顺序混乱的群组，一一校正
        foreach ($group_contests as $gc) {
            $gid = $gc->group_id;
            echo "[Wrong Order] Contests of Group " . $gid . " have incorrect order. Correct them to 1,2,3,...," . $gc->num_contests . PHP_EOL;
            // 对order字段赋值为(1,2,3,...,n)，update默认按主键升序依次赋值
            DBHelper::continue_order('group_contests', ['group_id' => $gid]);
            // $updated = DB::table('group_contests')
            //     ->leftJoin(DB::raw('(SELECT @row_num := 0) as row_num_table'), DB::raw('1'), DB::raw('1'))
            //     ->where('group_id', $gid)
            //     ->update(['order' => DB::raw('(@row_num:=@row_num+1)')]);
        }
    }
}
