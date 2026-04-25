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
        // attendance_id ではなく correction_requests テーブルの ID で検索
        $correction = CorrectionRequest::with(['attendance.rests', 'user'])->findOrFail($id);
        $attendance = $correction->attendance;

        // 画面に表示する値を「修正案」の内容に一時的に差し替える
        $attendance->clock_in = $correction->updated_clock_in;
        $attendance->clock_out = $correction->updated_clock_out;
        $attendance->remarks = $correction->remark;

        return view('admin.detail', [
            'attendance' => $attendance,
            'correction' => $correction, // 承認ボタン等で使うために渡す
            'mode' => 'approve'
        ]);
    }

    public function approve(Request $request, $id)
    {
        // 1. 修正申請データを取得
        $correction = CorrectionRequest::findOrFail($id);

        // 2. 本体の勤怠データ(Attendance)を修正案の内容で正式に更新
        $attendance = Attendance::findOrFail($correction->attendance_id);
        $attendance->update([
            'clock_in'  => $correction->updated_clock_in,
            'clock_out' => $correction->updated_clock_out,
            'remarks'   => $correction->remark,
            'status'    => '承認済み'
        ]);

        // 3. 修正申請側のステータスも「承認済み(2)」にする
        $correction->update(['status' => 2]);

        return redirect()->route('admin.correction.list')->with('message', '承認が完了しました');
    }
}
