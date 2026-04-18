<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
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

    public function staff(Request $request, $id)
    {
        // 1. 対象のスタッフ情報を取得
        $user = User::findOrFail($id);

        // 2. 表示する月を取得（なければ今月）
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $displayDate = Carbon::parse($month . '-01');

        // 3. 前月・翌月のリンク用文字列
        $prevMonth = $displayDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $displayDate->copy()->addMonth()->format('Y-m');

        // 4. そのスタッフの、指定された月の勤怠データを取得
        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        // 5. PG11用のブレードを表示
        return view('admin.staff_attendance', compact(
            'user',
            'attendances',
            'month',
            'displayDate',
            'prevMonth',
            'nextMonth'
        ));
    }
}
