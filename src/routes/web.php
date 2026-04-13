<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;

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
    });

    // 3. PG06: 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');
});

// --- 管理者用ルート ---
Route::prefix('admin')->group(function () {

    // 【ログイン前】PG07: 管理者ログイン画面の表示
    // ※ログイン処理(POST)はFortifyが「/admin/login」というURLで自動的に引き受けてくれます
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    // 【ログイン後のみアクセス可能】
    Route::middleware('auth:admin')->group(function () {

        // PG08: 全スタッフの勤怠一覧（とりあえずの戻り先）
        Route::get('/attendance/list', function () {
            return "管理者勤怠一覧画面（準備中）";
        })->name('admin.attendance.list');

        // 今後ここに PG09〜PG13 のルートを追記していきます

        // ログアウト（POST）
        // ※Fortifyのデフォルトは「/logout」ですが、管理者はここを叩くようにします
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    });
});