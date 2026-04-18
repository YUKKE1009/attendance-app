<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // statusカラムの後ろに、空でもOKな remarks カラムを追加する
            $table->text('remarks')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // ロールバック（取り消し）した時にカラムを消す設定
            $table->dropColumn('remarks');
        });
    }
}
