<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_updates', function (Blueprint $table) {
            $table->id();

            // 5番の修正申請テーブルに紐付け
            $table->foreignId('request_id')->constrained('correction_requests')->onDelete('cascade');

            // 4番の休憩レコード（どの休憩を直すか）に紐付け
            $table->foreignId('rest_id')->constrained('rests')->onDelete('cascade');

            // 休憩の修正案（開始・終了）
            $table->time('proposed_break_in');
            $table->time('proposed_break_out')->nullable();

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
        Schema::dropIfExists('rest_updates');
    }
}
