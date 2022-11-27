<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterIndexesAndAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // groups 添加索引
        Schema::table('groups', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('groups');

            if (!array_key_exists('groups_private_index', $indexesFound)) // 索引不存在则创建
                $table->boolean('private')->index()->default(1)->change();

            if (!array_key_exists('groups_hidden_index', $indexesFound)) // 索引不存在则创建
                $table->boolean('hidden')->index()->default(1)->change();
        });
        // solutions 添加索引
        Schema::table('solutions', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('solutions');
            if (!array_key_exists('solutions_submit_time_index', $indexesFound)) // 索引不存在则创建
                $table->dateTime('submit_time')->index()->useCurrent()->change();
        });

        /**
         * 以下添加的字段，是弥补某些已存在的表所缺失的功能，故增加之。
         */
        // contests添加字段
        Schema::table('contests', function (Blueprint $table) {
            if (!Schema::hasColumn('contests', 'num_members')) {
                $table->integer('num_members')->default(0); // 添加字段 参与成员数
            }
        });
        // contest_problems添加字段
        Schema::table('contest_problems', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_problems', 'accepted')) {
                $table->integer('solved')->default(0);
                $table->integer('accepted')->default(0);
                $table->integer('submitted')->default(0);
            }
        });
        // users添加字段
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'accepted')) {
                $table->integer('solved')->default(0);
                $table->integer('accepted')->default(0);
                $table->integer('submitted')->default(0);
            }
        });
        // problems添加字段
        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'accepted')) {
                $table->integer('solved')->default(0);
                $table->integer('accepted')->default(0);
                $table->integer('submitted')->default(0);
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
        //
    }
}
