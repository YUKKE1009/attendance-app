<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

// // ログイン済みユーザーのみアクセス可能にする（認証の保護）
// Route::middleware('auth')->group(function () {

    // PG03: 出勤登録画面（表示・打刻アクション）
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');
    });
// });
