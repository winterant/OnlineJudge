<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->default(0); // 添加字段 群组类型
            $table->boolean('hidden')->default(1);
            $table->boolean('private')->default(1);
            $table->string('name')->default('unamed');
            $table->text('description')->nullable();
            $table->string('teacher')->nullable()->comment('teacher\'s name');
            $table->string('class')->index()->nullable();
            $table->bigInteger('user_id')->index()->nullable();
            $table->integer('num_members')->default(0); // 添加字段 参与成员数
            $table->integer('num_problems')->default(0); // 添加字段 题目总数
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('group_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('group_id')->index();
            $table->bigInteger('user_id')->index();
            $table->tinyInteger('identity')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('group_contests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contest_id')->index();
            $table->bigInteger('group_id')->index();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_users');
        Schema::dropIfExists('group_contests');
    }
}
