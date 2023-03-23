<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 群组成员添加字段：archive
        Schema::table('group_users', function (Blueprint $table) {
            if (!Schema::hasColumn('group_users', 'archive')) {
                $table->json('archive')->nullable(); // 个人档案
            }
        });

        // ============================ 额外顺便工作 ============================
        Schema::table('groups', function (Blueprint $table) {
            // 群组删除字段 grade, class，并将class从数字类型改为字符串类型
            if (Schema::hasColumn('groups', 'grade')) {
                $table->dropColumn('grade');
            }
            if (Schema::hasColumn('groups', 'major')) {
                $table->dropColumn('major');
            }
            if (Schema::hasColumn('groups', 'class')) {
                $table->string('class')->nullable()->change();

                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('groups');
                if (!array_key_exists('groups_class_index', $indexesFound)) // 索引不存在则创建
                    $table->string('class')->index()->nullable()->change();
            }
            // 群组添加字段：type；0课程，1班级
            if (!Schema::hasColumn('groups', 'type')) {
                $table->tinyInteger('type')->default(0); // 添加字段 群组类型
            }
        });

        // 群组中的竞赛添加排序字段：order
        Schema::table('group_contests', function (Blueprint $table) {
            if (!Schema::hasColumn('group_contests', 'order')) {
                $table->integer('order')->default(0); // 排序字段
            }
        });

        // 顺便修复 problems表可能缺失samples字段
        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'samples')) {
                $table->json('samples')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_users', function (Blueprint $table) {
            if (Schema::hasColumn('group_users', 'archive')) {
                $table->dropColumn('archive');
            }
        });

        // 额外顺便工作： 恢复年级、专业字段
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'grade')) {
                $table->integer('grade')->nullable();
            }
            if (!Schema::hasColumn('groups', 'major')) {
                $table->string('major')->nullable();
            }
        });
    }
};
