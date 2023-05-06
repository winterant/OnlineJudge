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
            // 增加字段 spj_code
            if (!Schema::hasColumn('problems', 'spj_code')) {
                $table->text('spj_code')->nullable();
            }
            if (!Schema::hasColumn('problems', 'spj_language')) {
                $table->integer('spj_language')->default(14); // C++20 -O2
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
