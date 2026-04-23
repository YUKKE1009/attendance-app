<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return view('admin.detail', [
            'attendance' => $attendance,
            'mode' => 'edit'
        ]);
    }

    // 管理者の「修正」ボタンがここにつながります
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

    public function exportCsv(Request $request, $id)
    {
        // 1. 対象のユーザーと勤怠データを取得（staffメソッドと同様のロジック）
        $user = User::findOrFail($id);
        $month = $request->query('month', now()->format('Y-m'));
        $displayDate = \Carbon\Carbon::parse($month);

        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        // 2. CSVのレスポンスを作成
        $response = new StreamedResponse(function () use ($user, $displayDate, $attendances) {
            $handle = fopen('php://output', 'w');

            // 文字化け防止（Excel用）
            fputs($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // データ行の生成
            $startOfMonth = $displayDate->copy()->startOfMonth();
            $endOfMonth = $displayDate->copy()->endOfMonth();
            $weeks = ['日', '月', '火', '水', '木', '金', '土'];

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
                $dayOfWeek = $weeks[$date->dayOfWeek];

                $row = [
                    $date->format('m/d') . "($dayOfWeek)", // 日付
                    '', // 出勤
                    '', // 退勤
                    '', // 休憩
                    '', // 合計
                ];

                if ($attendance) {
                    // 出退勤時間
                    $row[1] = substr($attendance->clock_in, 0, 5);
                    $row[2] = $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '';

                    // 休憩・合計の計算（Bladeのロジックと同様に計算）
                    $totalRestMinutes = 0;
                    foreach ($attendance->rests as $rest) {
                        if ($rest->break_in && $rest->break_out) {
                            $in = \Carbon\Carbon::parse($rest->break_in);
                            $out = \Carbon\Carbon::parse($rest->break_out);
                            $totalRestMinutes += $in->diffInMinutes($out);
                        }
                    }
                    $row[3] = sprintf('%d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                    if ($attendance->clock_in && $attendance->clock_out) {
                        $start = \Carbon\Carbon::parse($attendance->clock_in);
                        $end = \Carbon\Carbon::parse($attendance->clock_out);
                        $workingMinutes = $start->diffInMinutes($end) - $totalRestMinutes;
                        $row[4] = sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                    }
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$user->name}_{$month}_attendance.csv",
        ]);

        return $response;
    }
}
