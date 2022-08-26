<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solutions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_id')->index();
            $table->bigInteger('contest_id')->index()->default(-1);
            $table->bigInteger('user_id')->index()->nullable();
            $table->string('judge_type', 5)->default('oi')->comment('acm,oi');
            $table->integer('result')->index()->default(0);
            $table->integer('time')->default(0);
            $table->float('memory')->default(0);
            $table->integer('language')->index()->default(0);
            $table->dateTime('submit_time')->useCurrent();
            $table->dateTime('judge_time')->useCurrent();
            $table->float('pass_rate')->default(1.0);
            $table->text('error_info')->nullable();
            $table->text('wrong_data')->nullable();
            $table->string('ip', 16)->index()->nullable();
            $table->string('ip_loc', 64)->nullable();
            $table->string('judger', 60)->nullable();
            $table->integer('code_length')->default(0);
            $table->text('code')->nullable();
            $table->integer('sim_rate')->default(0)->comment('0~100');
            $table->bigInteger('sim_sid')->nullable();
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
        Schema::dropIfExists('solutions');
    }
}
