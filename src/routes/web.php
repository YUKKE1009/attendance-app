<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {return view('welcome');});

// PG03 表示
Route::get('/attendance', [AttendanceController::class, 'index']);
Route::post('/attendance/clock-in', [AttendanceController::class, 'store']);
Route::post('/attendance/clock-out', [AttendanceController::class, 'update']); // 退勤
Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn']);  // 休憩入
Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut']); // 休憩戻
