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

    // 今後、勤怠一覧画面や申請画面を作る際も、この middleware('auth') の中に追加していきます
});
