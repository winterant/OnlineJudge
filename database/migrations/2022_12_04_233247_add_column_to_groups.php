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
        // 群组添加字段：type；0课程，1班级
        Schema::table('groups', function (Blueprint $table) {
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
    }
};
