<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedRestsToCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            // 休憩データをJSONで保存するためのカラムを追加（NULL許可）
            $table->text('updated_rests')->nullable()->after('updated_clock_out');
        });
    }

    public function down()
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            $table->dropColumn('updated_rests');
        });
    }
}
