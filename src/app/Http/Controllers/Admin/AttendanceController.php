<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $displayDate = Carbon::parse($date);

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.list', compact('attendances', 'displayDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        return view('admin.detail', compact('attendance'));
    }

    // ★ここを追加！管理者の「修正」ボタンがここにつながります
    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. 基本情報の更新
        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remarks'   => $request->remarks,
            'status'    => '承認済み',
        ]);

        // 2. 既存の休憩時間の更新
        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restData) {
                $attendance->rests()->where('id', $restId)->update([
                    'break_in'  => $restData['break_in'],
                    'break_out' => $restData['break_out'],
                ]);
            }
        }

        // 3. 新しい休憩（追加分）の保存
        // Blade側の input name="new_rest_in" と "new_rest_out" を受け取ります
        if ($request->filled(['new_rest_in', 'new_rest_out'])) {
            $attendance->rests()->create([
                'break_in'  => $request->new_rest_in,
                'break_out' => $request->new_rest_out,
            ]);
        }

        // 4. 一覧画面へ戻る
        return redirect()->route('admin.attendance.list', ['date' => $attendance->date])
            ->with('success', '勤怠情報を修正しました');
    }
}
