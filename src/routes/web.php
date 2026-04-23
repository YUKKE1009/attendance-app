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
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.attendance.list');
    }
    if (auth()->check()) {
        return redirect()->route('attendance.index');
    }
    return redirect('/login');
});

// --- 2. 申請一覧（管理者・一般ユーザー共通のパスと名前） ---
// URLが同じ場合、Laravelでは名前を1つに絞るのが最もエラーが起きにくい設計です
Route::middleware('auth:admin,web')->group(function () {
    // PG06 & PG12: 申請一覧
    Route::get('/stamp_correction_request/list', [CorrectionController::class, 'index'])
        ->name('admin.correction.list');
});

// --- 3. 管理者専用ルート（指示書パス：/adminなし） ---
Route::middleware('auth:admin')->group(function () {
    // PG13: 修正申請承認画面
    Route::get('/stamp_correction_request/approve/{id}', [CorrectionController::class, 'show'])
        ->name('admin.attendance.approve.show');

    Route::post('/stamp_correction_request/approve/{id}', [CorrectionController::class, 'approve'])
        ->name('admin.attendance.approve');
});

// --- 4. 一般ユーザー(web)のみアクセス可能なルート ---
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/clock-in', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/clock-out', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
        Route::post('/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');
        Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');
    });
    // ※ request.list という名前は削除し、admin.correction.list に統一します
});

// --- 5. ユーザー・管理者「共通」詳細ルート ---
Route::middleware('auth:admin,web')->group(function () {
    Route::prefix('attendance')->group(function () {
        Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
        Route::post('/detail/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update_request');
    });
});

// --- 6. 管理者用ルート (adminプレフィックス有り) ---
Route::prefix('admin')->group(function () {
    // 【ログイン前】
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');
    Route::post('/login', [Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);

    // 【ログイン後】
    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

        // 特定スタッフの勤怠一覧
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])->name('admin.staff.attendance');
        Route::get('/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.staff.export');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    });
});
