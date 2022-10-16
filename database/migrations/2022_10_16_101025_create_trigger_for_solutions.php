<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTriggerForSolutions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ============ 建立solutions的触发器，实时统计过题数量 ===========
        // `submitted` and `num_members`
        DB::unprepared(
            "CREATE TRIGGER trigger_solutions_insert
            AFTER INSERT on solutions FOR EACH ROW
            BEGIN
                UPDATE problems set submitted=submitted+1 where id=new.problem_id;
                UPDATE users set submitted=submitted+1 where id=new.user_id;
                UPDATE contest_problems set submitted=submitted+1 where contest_id=new.contest_id and problem_id=new.problem_id;
                UPDATE contests set num_members=num_members+1
                    where id=new.contest_id
                    and not exists(select 1 from solutions where id<new.id and contest_id=new.contest_id and `user_id`=new.user_id limit 1);
            END"
        );
        // `accepted` and `solved`
        // 注意如果提交记录被重判，请在[后台管理-题目管理-重判]中手动矫正solved,accepted字段
        DB::unprepared(
            "CREATE TRIGGER trigger_solutions_update
            AFTER UPDATE on solutions FOR EACH ROW
            BEGIN
                UPDATE problems set accepted=accepted+1 where id=new.problem_id and old.result!=4 and new.result=4;
                UPDATE problems set solved=solved+1 where id=new.problem_id and old.result!=4 and new.result=4
                    and not exists(select 1 from solutions where id<new.id and problem_id=new.problem_id and result=4 limit 1);
                UPDATE users set accepted=accepted+1 where id=new.user_id and old.result!=4 and new.result=4;
                UPDATE users set solved=solved+1 where id=new.user_id and old.result!=4 and new.result=4
                    and not exists(select 1 from solutions where id<new.id and `user_id`=new.user_id and result=4 limit 1);
                UPDATE contest_problems set accepted=accepted+1 where problem_id=new.problem_id and contest_id=new.contest_id and old.result!=4 and new.result=4;
                UPDATE contest_problems set solved=solved+1 where problem_id=new.problem_id and contest_id=new.contest_id and old.result!=4 and new.result=4
                    and not exists(select 1 from solutions where id<new.id and problem_id=new.problem_id and contest_id=new.contest_id and result=4 limit 1);
            END"
        );
        // 一些其它更新: 竞赛类别的父类别默认为0而不是null
        Schema::table('contest_cate', function (Blueprint $table) {
            DB::table('contest_cate')->where('parent_id', null)->update(['parent_id' => 0]);
            $table->bigInteger('parent_id')->nullable(false)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_solutions_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_solutions_update');
    }
}
