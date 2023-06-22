<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // solutions
        Schema::table('solutions', function (Blueprint $table) {
            // 添加字段 sim_report 查重报告
            if (!Schema::hasColumn('solutions', 'sim_report')) {
                $table->json('sim_report')->nullable();
            }
            // 重命名字段 judge0result -> judge_result
            if (Schema::hasColumn('solutions', 'judge0result')) {
                $table->renameColumn('judge0result', 'judge_result');
            }
            // 删除不使用的触发器 todo
        });

        // problems
        Schema::table('problems', function (Blueprint $table) {
            // 增加字段 tags
            if (!Schema::hasColumn('problems', 'tags')) {
                $table->json('tags')->nullable();
            }
            if (!Schema::hasColumn('problems', 'spj_language')) {
                $table->integer('spj_language')->default(14); // C++20 -O2
            }
            if (!Schema::hasColumn('problems', 'level')) {
                $table->integer('level')->default(0)->comment('0:null,1:easy,2:middle,3:difficult');
            }
            // 改名
            if (Schema::hasColumn('problems', 'creator')) {
                $table->renameColumn('creator', 'user_id');
            }
        });
        // 标签池 添加 创建者
        Schema::table('tag_pool', function (Blueprint $table) {
            if (Schema::hasColumn('tag_pool', 'parent_id')) {
                $table->renameColumn('parent_id', 'user_id');
            }
        });
        // contests
        Schema::table('contests', function (Blueprint $table) {
            // 删除没用的字段
            if (Schema::hasColumn('contests', 'judge_instantly')) {
                $table->dropColumn('judge_instantly');
            }
            // 改名
            if (Schema::hasColumn('contests', 'open_discussion')) {
                $table->renameColumn('open_discussion', 'enable_discussing');
            }
            // 添加 是否收集标签
            if (!Schema::hasColumn('contests', 'enable_tagging')) {
                $table->boolean('enable_tagging')->default(0);
            }
            // 添加 题目分节
            if (!Schema::hasColumn('contests', 'sections')) {
                $table->json('sections')->nullable(); // 分节信息 [{'name':'Sample Section','start':int}, ...]
            }
        });
        Schema::table('contest_notices', function (Blueprint $table) {
            if (Schema::hasColumn('contest_notices', 'creator')) {
                $table->renameColumn('creator', 'user_id');
            }
        });

        // groups 群组增加题数、人数等字段
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'num_members')) {
                $table->integer('num_members')->default(0); // 添加字段 参与成员数
            }
            if (!Schema::hasColumn('groups', 'num_problems')) {
                $table->integer('num_problems')->default(0); // 添加字段 题目总数
            }
            if (!Schema::hasColumn('groups', 'unlock_contest')) {
                $table->boolean('unlock_contest')->default(0); // 若为1，则password竞赛在前一场全部AC时，将显示自己进入密码
            }
            // 改名
            if (Schema::hasColumn('groups', 'creator')) {
                $table->renameColumn('creator', 'user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // problems
        Schema::table('problems', function (Blueprint $table) {
            // 恢复字段名
            if (Schema::hasColumn('problems', 'user_id')) {
                $table->renameColumn('user_id', 'creator');
            }
        });

        // contests
        Schema::table('contests', function (Blueprint $table) {
            // 恢复名字
            if (Schema::hasColumn('contests', 'enable_discussing')) {
                $table->renameColumn('enable_discussing', 'open_discussion');
            }
        });
        Schema::table('contest_notices', function (Blueprint $table) {
            // 恢复字段名
            if (Schema::hasColumn('contest_notices', 'user_id')) {
                $table->renameColumn('user_id', 'creator');
            }
        });

        // groups
        Schema::table('groups', function (Blueprint $table) {
            // 恢复字段名
            if (Schema::hasColumn('groups', 'user_id')) {
                $table->renameColumn('user_id', 'creator');
            }
        });
    }
};
