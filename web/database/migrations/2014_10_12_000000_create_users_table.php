<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 60)->unique();
            $table->string('email', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 60);

            $table->string('nick', 50)->nullable()->index();
            $table->string('school', 50)->nullable()->index();
            $table->string('class', 50)->nullable()->index();
            $table->boolean('revise')->default(1)->comment('允许修改个人信息');
            $table->boolean('locked')->default(0)->comment('锁定：禁止用户访问网站');

            $table->integer('solved')->default(0);
            $table->integer('accepted')->default(0);
            $table->integer('submitted')->default(0);

            $table->string('api_token', 65)->unique()->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
