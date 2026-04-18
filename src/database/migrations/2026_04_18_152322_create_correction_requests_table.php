<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            // * id: bigint unsigned / PRIMARY KEY / NOT NULL / 修正申請ID
            $table->id();

            // * user_id: bigint unsigned / FOREIGN KEY(users.id) / NOT NULL / ユーザーID
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // * attendance_id: bigint unsigned / FOREIGN KEY(attendances.id) / NOT NULL / 勤怠ID
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');

            // * status: tinyint / NOT NULL / 状態（1:承認待ち, 2:承認済み）
            $table->tinyInteger('status')->default(1)->comment('1:承認待ち, 2:承認済み');

            // * target_date: date / NOT NULL / 修正対象日
            $table->date('target_date');

            // * remark: text / NOT NULL / 修正申請の理由
            $table->text('remark');

            // * updated_clock_in: time / NULL / 修正後の出勤時刻
            $table->time('updated_clock_in')->nullable();

            // * updated_clock_out: time / NULL / 修正後の退勤時刻
            $table->time('updated_clock_out')->nullable();

            // * created_at: timestamp / NULL / 作成日時
            // * updated_at: timestamp / NULL / 更新日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
