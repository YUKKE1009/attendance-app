<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $statusValue = ($status === 'approved') ? 2 : 1;

        // 1. クエリの基本形
        $query = \App\Models\CorrectionRequest::with(['user', 'attendance'])
            ->where('status', $statusValue);

        // 2.管理者でない（一般スタッフ）なら、自分の分だけに絞り込む
        if (!auth()->guard('admin')->check()) {
            $query->where('user_id', auth()->id());
        }

        $requests = $query->get();

        // 3. 表示するViewを分ける
        if (auth()->guard('admin')->check()) {
            return view('admin.request', compact('requests', 'status'));
        }

        return view('request.list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $correction = CorrectionRequest::with(['attendance.rests', 'user'])->findOrFail($id);
        $attendance = $correction->attendance;

        // 1. 出退勤と備考を申請内容に差し替え
        $attendance->clock_in = $correction->updated_clock_in;
        $attendance->clock_out = $correction->updated_clock_out;
        $attendance->remarks = $correction->remark;

        // 💡 2. 休憩データ(JSON)を復元して表示に反映させる
        if ($correction->updated_rests) {
            $restsData = json_decode($correction->updated_rests, true);

            // 既存の休憩リレーションの内容を、申請された値で上書き
            if (isset($restsData['existing'])) {
                foreach ($attendance->rests as $rest) {
                    if (isset($restsData['existing'][$rest->id])) {
                        $rest->break_in = $restsData['existing'][$rest->id]['break_in'];
                        $rest->break_out = $restsData['existing'][$rest->id]['break_out'];
                    }
                }
            }

            // 💡 3. 表示から「空（消された）」休憩を除外する
            // これにより、画面上で休憩2、休憩3が消えて見えます
            $attendance->setRelation('rests', $attendance->rests->filter(function ($rest) {
                return !empty($rest->break_in);
            }));
        }

        $isPending = ($correction->status == 1);

        return view('admin.detail', [
            'attendance' => $attendance,
            'correction' => $correction,
            'mode' => 'approve',
            'isPending' => $isPending
        ]);
    }

    public function approve(Request $request, $id)
    {
        $correction = CorrectionRequest::findOrFail($id);
        $attendance = Attendance::findOrFail($correction->attendance_id);

        // 1. 本体の更新
        $attendance->update([
            'clock_in'  => $correction->updated_clock_in,
            'clock_out' => $correction->updated_clock_out,
            'remarks'   => $correction->remark,
            'status'    => '承認済み'
        ]);

        // 💡 2. 休憩データの反映 (DBの値を実際に書き換える)
        if ($correction->updated_rests) {
            $restsData = json_decode($correction->updated_rests, true);
            if (isset($restsData['existing'])) {
                foreach ($restsData['existing'] as $restId => $times) {
                    // 両方空なら削除、そうでなければ更新
                    if (empty($times['break_in']) && empty($times['break_out'])) {
                        \App\Models\Rest::destroy($restId);
                    } else {
                        \App\Models\Rest::where('id', $restId)->update([
                            'break_in'  => $times['break_in'],
                            'break_out' => $times['break_out'],
                        ]);
                    }
                }
            }
        }

        $correction->update(['status' => 2]);

        return redirect()->route('admin.correction.list')->with('message', '承認が完了しました');
    }
}
