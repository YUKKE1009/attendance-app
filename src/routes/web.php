<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CorrectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- 1. トップページのリダイレクト設定 ---
Route::get('/', function () {
    // 管理者としてログイン中なら
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.attendance.list');
    }
    // 一般ユーザーとしてログイン中なら
    if (auth()->check()) {
        return redirect()->route('attendance.index');
    }
    // 未ログインなら一般ログイン画面へ
    return redirect('/login');
});

// --- 2. ユーザー・管理者「共通」でアクセス可能なルート ---
// PG05: 勤怠詳細画面は、管理者も確認する必要があるため auth:admin を追加
Route::middleware('auth:admin,web')->group(function () {
    Route::prefix('attendance')->group(function () {
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
        Route::post('/detail/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update_request');
    });
});

// --- 3. 一般ユーザー(web)のみアクセス可能なルート ---
Route::middleware('auth:web')->group(function () {
    Route::prefix('attendance')->group(function () {
        // PG03: 出勤登録画面
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

        // PG04: 勤怠一覧画面
        Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');
    });

    // PG06: 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');
});

// --- 4. 管理者用ルート ---
// --- 4. 管理者用ルート ---
Route::prefix('admin')->group(function () {

    // 【ログイン前】PG07: 管理者ログイン画面
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    // ログイン処理
    Route::post('/login', [Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);

    // 【ログイン後のみアクセス可能】
    Route::middleware('auth:admin')->group(function () {

        // PG08: 今日の勤怠一覧（全スタッフ）
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

        // PG11: スタッフ別勤怠一覧（特定のスタッフの月次リスト）
        // ※ /admin/attendance/staff/{id} にアクセス
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])->name('admin.staff.attendance');

        // PG05相当: 勤怠詳細（特定の1日の修正画面）
        // ※ IDの重複を避けるため staff/{id} の下に配置
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');

        // 修正保存
        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

        // PG10: スタッフ一覧
        Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');

        // PG12: 申請一覧画面
        Route::get('/stamp_correction_request/list', [CorrectionController::class, 'index'])
            ->name('admin.correction.list');

        // PG13相当: 申請詳細画面
        Route::get('/stamp_correction_request/detail/{id}', [CorrectionController::class, 'show'])
            ->name('admin.correction.detail');

        // PG13 承認処理本体
        // POSTで受け取り、CorrectionControllerのapproveメソッドを呼び出す
        Route::post('/stamp_correction_request/approve/{id}', [CorrectionController::class, 'approve'])
            ->name('admin.attendance.approve');

        // ログアウト
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    });
});