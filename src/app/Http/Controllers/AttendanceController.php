<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
        $userId = Auth::id();
        // もしログインしてなければエラーにする（安全策）
        if (!$userId) {
            return redirect('/login');
        }

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
        // 1. 元のデータを取得
        $attendance = Attendance::with(['rests', 'user'])->findOrFail($id);

        // 2. この勤怠に対する「承認待ち」の申請があるか探す
        $correctionRequest = \App\Models\CorrectionRequest::where('attendance_id', $id)
            ->where('status', 1) // 1: 承認待ち
            ->first();

        $isPending = ($attendance->status === '承認待ち');

        // 💡 3. 承認待ちなら、表示するデータを「申請内容」ですり替える！
        if ($isPending && $correctionRequest) {
            $attendance->clock_in = $correctionRequest->updated_clock_in;
            $attendance->clock_out = $correctionRequest->updated_clock_out;
            $attendance->remarks = $correctionRequest->remark;

            // 💡 休憩データ(JSON)をデコードして、一時的に rests リレーションを上書きする
            $restsData = json_decode($correctionRequest->updated_rests, true);

            // 申請データの中にある休憩情報を、画面表示用のコレクションに変換
            if (isset($restsData['existing'])) {
                // 既存の休憩を、申請された値に置き換えて表示
                foreach ($attendance->rests as $rest) {
                    if (isset($restsData['existing'][$rest->id])) {
                        $rest->break_in = $restsData['existing'][$rest->id]['break_in'];
                        $rest->break_out = $restsData['existing'][$rest->id]['break_out'];
                    }
                }
            }

            // ★重要：もし申請時に「消した（空にした）」休憩があれば、
            // コレクションから除外して表示されないようにする処理もここで行えます。
            $attendance->setRelation('rests', $attendance->rests->filter(function ($rest) {
                return !empty($rest->break_in); // 開始時間があるものだけ表示
            }));
        }

        return view('attendance.detail', compact('attendance', 'isPending'));
    }

    // 修正申請の実行処理 (FN030)
    public function updateRequest(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. 秒補完のロジック（既存のものを流用）
        $clockIn = $request->clock_in;
        if ($clockIn && strlen($clockIn) === 5) $clockIn .= ':00';
        $clockOut = $request->clock_out;
        if ($clockOut && strlen($clockOut) === 5) $clockOut .= ':00';

        // 💡 2. 休憩データをJSONに変換する（新規・既存・追加分をまとめる）
        // $request->rests は既存分、$request->new_rest_inなどは新規分です
        $restsData = [
            'existing' => $request->rests, // 既存の休憩（ID付き）
            'new' => [
                'break_in' => $request->new_rest_in,
                'break_out' => $request->new_rest_out,
            ]
        ];

        // 3. correction_requests テーブルに保存
        \App\Models\CorrectionRequest::create([
            'user_id'           => \Illuminate\Support\Facades\Auth::id(),
            'attendance_id'     => $attendance->id,
            'status'            => 1, // 1:承認待ち
            'target_date'       => $attendance->date,
            'remark'            => $request->remarks,
            'updated_clock_in'  => $clockIn,
            'updated_clock_out' => $clockOut,
            'updated_rests'     => json_encode($restsData), // 💡 ここでJSON化して保存！
        ]);

        // 4. 勤怠本体は「ステータス」のみ更新（時間は変えない）
        $attendance->update([
            'status' => '承認待ち',
        ]);

        return redirect()->route('attendance.list')->with('success', '修正申請を提出しました。承認されるまで一覧には反映されません。');
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
