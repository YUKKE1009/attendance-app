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

        $query = \App\Models\CorrectionRequest::with(['user', 'attendance'])
            ->where('status', $statusValue);

        if (!auth()->guard('admin')->check()) {
            $query->where('user_id', auth()->id());
        }

        $requests = $query->get();

        if (auth()->guard('admin')->check()) {
            return view('admin.request', compact('requests', 'status'));
        }

        return view('request.list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $correction = CorrectionRequest::with(['attendance.rests', 'user'])->findOrFail($id);
        $attendance = $correction->attendance;

        $attendance->clock_in = $correction->updated_clock_in;
        $attendance->clock_out = $correction->updated_clock_out;
        $attendance->remarks = $correction->remark;

        if ($correction->updated_rests) {
            $restsData = json_decode($correction->updated_rests, true);

            if (isset($restsData['existing'])) {
                foreach ($attendance->rests as $rest) {
                    if (isset($restsData['existing'][$rest->id])) {
                        $rest->break_in = $restsData['existing'][$rest->id]['break_in'];
                        $rest->break_out = $restsData['existing'][$rest->id]['break_out'];
                    }
                }
            }

            if (isset($restsData['new'])) {
                $newRest = new \App\Models\Rest([
                    'break_in'  => $restsData['new']['break_in'],
                    'break_out' => $restsData['new']['break_out'],
                ]);
                $attendance->rests->push($newRest);
            }

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

        // 💡 重要：すでに承認済み（status=2）なら処理を中断してリダイレクトさせる
        if ($correction->status == 2) {
            return redirect()->route('admin.correction.list')->with('error', 'この申請は既に承認済みです。');
        }

        // --- 1. 本体（Attendance）の更新 ---
        $attendance->update([
            'clock_in'  => $correction->updated_clock_in,
            'clock_out' => $correction->updated_clock_out,
            'remarks'   => $correction->remark,
            'status'    => '承認済み'
        ]);

        // --- 2. 休憩データの反映 ---
        if ($correction->updated_rests) {
            $restsData = json_decode($correction->updated_rests, true);

            // 既存休憩の更新・削除
            if (isset($restsData['existing'])) {
                foreach ($restsData['existing'] as $restId => $times) {
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

            // 💡 3. 新規休憩の保存
            // statusが1の時（＝今回初めて承認する時）だけ実行するようにガードをかける
            if (isset($restsData['new'])) {
                $attendance->rests()->create([
                    'break_in'  => $restsData['new']['break_in'],
                    'break_out' => $restsData['new']['break_out'],
                ]);
            }
        }

        // --- 4. 申請を承認済みに更新 ---
        $correction->update(['status' => 2]);

        return redirect()->route('admin.correction.list')->with('message', '承認が完了しました');
    }
}