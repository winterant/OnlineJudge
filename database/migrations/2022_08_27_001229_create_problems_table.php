<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->index()->default(0)->comment('0:编程,1:代码填空');
            $table->string('title')->default('unamed');
            $table->text('description')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->text('hint')->nullable();
            $table->string('source')->nullable();
            $table->json('tags')->nullable();  // ['***','***', ...]
            $table->json('samples')->nullable();  // [{'in':'***','out':'***'}, ...]
            $table->text('fill_in_blank')->nullable();
            $table->integer('language')->default(0)->comment('代码填空的语言');
            $table->boolean('spj')->default(0); // 是否spj
            $table->integer('spj_language')->default(14); // C++20 -O2
            $table->integer('time_limit')->default(1000)->comment('MS');
            $table->integer('memory_limit')->default(1024)->comment('MB');
            $table->boolean('hidden')->index()->default(1);
            $table->bigInteger('user_id')->nullable();

            $table->integer('level')->default(0)->comment('0:null,1:easy,2:middle,3:difficult');
            $table->integer('solved')->default(0);
            $table->integer('accepted')->default(0);
            $table->integer('submitted')->default(0);

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
        DB::statement("ALTER TABLE problems AUTO_INCREMENT=1000;");

        Schema::create('discussions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_id')->index();
            $table->bigInteger('discussion_id')->nullable();
            $table->string('reply_username')->nullable();
            $table->string('username', 60)->nullable();
            $table->text('content')->nullable();
            $table->integer('top')->default(0);
            $table->boolean('hidden')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('tag_marks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('problem_id')->index();
            $table->bigInteger('user_id')->index();
            $table->bigInteger('tag_id')->index();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('tag_pool', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->bigInteger('user_id')->nullable(); // 创建者
            $table->boolean('hidden')->default(0);
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
        Schema::dropIfExists('problems');
        Schema::dropIfExists('discussions');
        Schema::dropIfExists('tag_marks');
        Schema::dropIfExists('tag_pool');
    }
}
