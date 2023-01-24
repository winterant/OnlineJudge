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
        // 群组删除字段：grade, class，并将class从数字类型改为字符串类型
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'grade')) {
                $table->dropColumn('grade');
            }
            if (Schema::hasColumn('groups', 'major')) {
                $table->dropColumn('major');
            }
            if (Schema::hasColumn('groups', 'class')) {
                $table->string('class')->nullable()->index()->change();
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
        // 恢复
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
