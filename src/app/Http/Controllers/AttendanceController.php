<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest; // 休憩モデルも使う場合は追加
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 表示処理
    public function index()
    {
        $userId = Auth::id() ?? 1;
        $today = Carbon::now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        return view('attendance.attendance', compact('attendance'));
    }

    // 出勤処理
    // 「出勤」ボタンが押された時の保存処理
    public function store(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $now = Carbon::now();

        $attendance = Attendance::create([
            'user_id'  => $userId,
            'date'     => $now->format('Y-m-d'),
            'clock_in' => $now->format('H:i:s'),
            'status'   => '出勤中',
        ]);

        return redirect('/attendance');
    }

    // 退勤処理
    public function update(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $today = Carbon::now()->format('Y-m-d');

        Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
                'status'    => '退勤済',
            ]);

        return redirect('/attendance');
    }

    // 休憩入処理
    public function breakIn(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $today = Carbon::now()->format('Y-m-d');

        // 1. 親となる今日の出勤データを探す
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            // 2. 出勤データのステータスを「休憩中」に更新
            $attendance->update(['status' => '休憩中']);

            // 3. 【追加！】restsテーブルに新しい行を作成して保存
            Rest::create([
                'attendance_id' => $attendance->id,
                'break_in'      => Carbon::now()->format('H:i:s'),
            ]);
        }

        return redirect('/attendance');
    }

    // 休憩戻処理
    public function breakOut(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $today = Carbon::now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            // 1. 出勤データのステータスを「出勤中」に戻す
            $attendance->update(['status' => '出勤中']);

            // 2. 【追加！】restsテーブルの「まだ終わっていない（break_outが空）休憩」を探して、終了時間を記録
            Rest::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->update(['break_out' => Carbon::now()->format('H:i:s')]);
        }

        return redirect('/attendance');
    }
}
