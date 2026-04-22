<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class CorrectionController extends Controller
{
    /**
     * PG12: 申請一覧画面（管理者）
     * パス: /stamp_correction_request/list
     */
    // app/Http/Controllers/Admin/CorrectionController.php

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $statusValue = ($status === 'approved') ? '承認済み' : '承認待ち';

        // 1. 管理者の場合（既存のコード）
        if (auth()->guard('admin')->check()) {
            $requests = \App\Models\Attendance::with('user')->where('status', $statusValue)->get();
            return view('admin.request', compact('requests', 'status'));
        }

        // 2. 一般ユーザーの場合（ここを修正！）
        $requests = \App\Models\Attendance::where('user_id', auth()->id())
            ->where('status', $statusValue)
            ->get();

        // resources/views/request/list.blade.php を呼び出す
        return view('request.list', compact('requests', 'status'));
    }


    /**
     * PG13: 修正申請承認画面（管理者）
     * パス: /stamp_correction_request/approve/{id}
     */
    public function show($id)
    {
        // 申請データ（Attendance）を取得
        $attendance = Attendance::with(['rests', 'user'])->findOrFail($id);

        // 管理者用詳細画面を表示（承認モード）
        return view('admin.detail', [
            'attendance' => $attendance,
            'mode' => 'approve' // Blade側で「承認」ボタンを表示させるためのフラグ
        ]);
    }

    /**
     * PG13: 修正申請の承認処理
     * 実行後、PG12の申請一覧画面へリダイレクト
     */
    public function approve(Request $request, $id)
    {
        // 1. 該当の勤怠データを取得
        $attendance = Attendance::findOrFail($id);

        // 2. ステータスを「承認済み」に更新
        // ※実際の運用ではここで修正内容の反映ロジックが入る場合もありますが、
        // 現状のステータス更新仕様に基づき実装
        $attendance->update([
            'status' => '承認済み'
        ]);

        // 3. 申請一覧画面（PG12）へリダイレクト
        // route('admin.correction.list') は /stamp_correction_request/list を指します
        return redirect()->route('admin.correction.list')->with('message', '承認が完了しました');
    }
}
