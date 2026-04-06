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

    // 【勤怠関連ルート】
    Route::prefix('attendance')->group(function () {
        // PG03: 出勤登録画面（表示・打刻アクション）
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

        // PG04: 勤怠一覧画面（コントローラーの list メソッドを呼ぶ）
        Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');

        // PG05: 勤怠詳細画面（コントローラーの show メソッドを呼ぶ）
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    });

    // 3. PG06: 申請一覧（attendanceの箱の外、でもauthの箱の中）
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');
});