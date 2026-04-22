<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// ★ FormRequestをuseに追加
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    // 表示処理
    /**
     * 打刻画面（出勤登録画面）の表示
     */
    public function index()
    {
        $userId = Auth::id() ?? 1;
        $today = Carbon::now()->format('Y-m-d');

        // 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        // --- 画面表示用のステータスを判定するロジック ---
        $displayStatus = '勤務外';

        if ($attendance) {
            // 1. 打刻フロー中のステータス（出勤中・休憩中）ならそのまま表示
            if ($attendance->status === '出勤中' || $attendance->status === '休憩中') {
                $displayStatus = $attendance->status;
            }
            // 2. 退勤打刻が済んでいる、または申請系（承認待ち・承認済み）のステータスの場合
            elseif ($attendance->clock_out || $attendance->status === '退勤済' || $attendance->status === '承認待ち' || $attendance->status === '承認済み') {
                $displayStatus = '退勤済';
            }
            // 3. それ以外（出勤打刻はあるが上記に当てはまらない場合）
            else {
                $displayStatus = '出勤中';
            }
        }

        // Bladeに $attendance と $displayStatus の両方を渡す
        return view('attendance.attendance', compact('attendance', 'displayStatus'));
    }
       

    // 出勤処理
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

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $attendance->update(['status' => '休憩中']);

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
            $attendance->update(['status' => '出勤中']);

            Rest::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->update(['break_out' => Carbon::now()->format('H:i:s')]);
        }

        return redirect('/attendance');
    }

    public function list(Request $request)
    {
        $userId = Auth::id();
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::with('rests')
            ->where('user_id', $userId)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        return view('attendance.list', compact('attendances', 'month'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['rests', 'user'])->findOrFail($id);
        $isPending = ($attendance->status === '承認待ち');

        return view('attendance.detail', compact('attendance', 'isPending'));
    }

    // 修正申請の実行処理 (FN030)
    public function updateRequest(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 秒補完のロジック
        $clockIn = $request->clock_in;
        if ($clockIn && strlen($clockIn) === 5) $clockIn .= ':00';

        $clockOut = $request->clock_out;
        if ($clockOut && strlen($clockOut) === 5) $clockOut .= ':00';

        // 1. 勤怠本体の更新とステータスを「承認待ち」に変更
        $attendance->update([
            'clock_in'  => $clockIn, // 補完後の変数を使う
            'clock_out' => $clockOut,
            'remarks'   => $request->remarks,
            'status'    => '承認待ち',
        ]);

        // 2. 既存の休憩データの更新（空なら削除・スキップ）
        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restData) {
                $in = $restData['break_in'];
                $out = $restData['break_out'];

                // ★【両方ブランク】ならDBから削除
                if (empty($in) && empty($out)) {
                    Rest::destroy($restId);
                    continue;
                }

                // ★【片方だけ入力】なら更新せずに無視（不完全なデータを防ぐ）
                if (empty($in) || empty($out)) {
                    continue;
                }

                // 両方入力されている場合のみ更新（秒補完も考慮）
                Rest::where('id', $restId)->update([
                    'break_in'  => (strlen($in) === 5) ? $in . ':00' : $in,
                    'break_out' => (strlen($out) === 5) ? $out . ':00' : $out,
                ]);
            }
        }

        // 3. 新規追加分の休憩保存（両方入力されている時だけ保存）
        if (!empty($request->new_rest_in) && !empty($request->new_rest_out)) {
            $newIn = $request->new_rest_in;
            $newOut = $request->new_rest_out;

            Rest::create([
                'attendance_id' => $attendance->id,
                'break_in'      => (strlen($newIn) === 5) ? $newIn . ':00' : $newIn,
                'break_out'     => (strlen($newOut) === 5) ? $newOut . ':00' : $newOut,
            ]);
        }

        return redirect()->route('admin.correction.list')->with('message', '修正申請を出しました');
    }

    // ★PG06: 申請一覧画面の表示
    public function requestList(Request $request)
    {
        $userId = Auth::id();

        // クエリパラメータから status を取得（デフォルトは pending:承認待ち）
        $status = $request->query('status', 'pending');

        // 状態をDBの値（承認待ち or 承認済み）に変換
        // ※もしDBに 1 or 2 で入れている場合はここを数字に変更してください
        $statusValue = ($status === 'approved') ? '承認済み' : '承認待ち';

        $attendances = Attendance::where('user_id', $userId)
            ->where('status', $statusValue)
            ->orderBy('updated_at', 'desc')
            ->get();

        // status変数も一緒にViewに渡すことで、タブのactive判定に使えます
        return view('request.list', compact('attendances', 'status'));
    }
}
