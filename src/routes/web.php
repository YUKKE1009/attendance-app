<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ (http://localhost/) の挙動
Route::get('/', function () {
    // ログインしていれば勤怠画面へ、していなければログイン画面へリダイレクト
    return auth()->check() ? redirect()->route('attendance.index') : redirect('/login');
});

// ログイン済みユーザーのみアクセス可能なルート（認証の保護）
Route::middleware('auth')->group(function () {

    // PG03: 出勤登録画面（表示・打刻アクション）
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');
    });

    /// PG04 勤怠一覧画面
    Route::get('/attendance/list', function () {
        return '<h2>PG04: 勤怠一覧画面（作成中）</h2>';
    })->name('attendance.list');

    //：PG06 申請一覧画面
    Route::get('/stamp_correction_request/list', function () {
        return '<h2>PG06: 申請一覧画面（作成中）</h2>';
    })->name('request.list');

    //：PG05 勤怠詳細画面（後で使う用）
    Route::get('/attendance/detail/{id}', function ($id) {
        return "<h2>PG05: 勤怠詳細画面 ID:{$id}（作成中）</h2>";
    })->name('attendance.detail');
});
