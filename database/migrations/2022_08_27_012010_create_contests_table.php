<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateContestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('judge_type', 5)->default('acm');
            $table->boolean('enable_discussing')->default(0); // 是否允许在比赛时使用讨论版
            $table->boolean('enable_tagging')->default(0); // 是否允许用户过题后给题目打标签
            $table->string('title')->default('unamed');
            $table->text('description')->nullable();
            $table->bigInteger('allow_lang')->default(15)->comment('按位标记允许的语言');
            $table->dateTime('start_time')->index()->useCurrent();
            $table->dateTime('end_time')->index()->useCurrent();
            $table->float('lock_rate')->default(0)->comment('0~1,封榜比例');
            $table->boolean('public_rank')->default(0);
            $table->string('access', 10)->default('public')->comment('public,password,private');
            $table->string('password', 40)->nullable();
            $table->bigInteger('user_id')->index()->nullable();
            $table->integer('num_members')->default(0); // 添加字段 参与成员数
            $table->boolean('hidden')->index()->default(0);
            $table->bigInteger('order')->index()->default(0);
            $table->bigInteger('cate_id')->index()->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
        DB::statement("ALTER TABLE contests AUTO_INCREMENT=1000;");

        Schema::create('contest_cate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->default('unamed');
            $table->text('description')->nullable();
            $table->boolean('hidden')->default(0);
            $table->bigInteger('order')->index()->default(0);
            $table->bigInteger('parent_id')->index()->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('contest_balloons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('solution_id')->nullable();
            $table->boolean('sent')->default(0);
            $table->dateTime('send_time')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('contest_notices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contest_id')->index()->nullable();
            $table->string('title')->default('unamed');
            $table->text('content')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('contest_problems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contest_id')->index();
            $table->integer('index');
            $table->bigInteger('problem_id')->index();

            $table->integer('solved')->default(0);
            $table->integer('accepted')->default(0);
            $table->integer('submitted')->default(0);

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('contest_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contest_id')->index();
            $table->bigInteger('user_id')->index();
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
        Schema::dropIfExists('contests');
        Schema::dropIfExists('contest_cate');
        Schema::dropIfExists('contest_balloons');
        Schema::dropIfExists('contest_notices');
        Schema::dropIfExists('contest_problems');
        Schema::dropIfExists('contest_users');
    }
}
