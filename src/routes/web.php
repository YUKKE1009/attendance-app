<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ
Route::get('/', function () {
    return auth()->check() ? redirect()->route('attendance.index') : redirect('/login');
});

// ログイン済みユーザーのみアクセス可能なルート
Route::middleware('auth')->group(function () {

    // 【勤怠関連ルート】
    Route::prefix('attendance')->group(function () {
        // PG03: 出勤登録画面
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

        // PG04: 勤怠一覧画面
        Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');

        // PG05: 勤怠詳細画面
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
        Route::post('/detail/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update_request');
    }); // ← ここ！この閉じカッコが抜けていました

    // 3. PG06: 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');
}); // ← middlewareグループの閉じ