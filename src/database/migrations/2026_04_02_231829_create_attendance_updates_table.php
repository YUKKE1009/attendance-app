<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_updates', function (Blueprint $table) {
            $table->id();

            // 5番の correction_requests テーブルと紐付け
            $table->foreignId('request_id')->constrained('correction_requests')->onDelete('cascade');

            // 修正案の出勤・退勤時間
            $table->time('proposed_clock_in');
            $table->time('proposed_clock_out')->nullable(); // 退勤は空の場合もあるため nullable

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_updates');
    }
}
