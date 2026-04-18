<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// スタッフ側と同じモデルを使う
use App\Models\Attendance;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        // 1. タブの状態を取得（デフォルトは pending）
        $status = $request->query('status', 'pending');

        // 2. スタッフ側のロジックに合わせて表示する文字列を決定
        $statusValue = ($status === 'approved') ? '承認済み' : '承認待ち';

        // 3. 管理者なので user_id の絞り込みはせず、全ユーザーのデータを取得
        // スタッフ側と同じく Attendance モデルから取得
        $requests = Attendance::with('user')
            ->where('status', $statusValue)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.request', compact('requests', 'status'));
    }

    public function show($id)
    {
        // 申請データ（Attendance）を取得
        $attendance = Attendance::with(['rests', 'user'])->findOrFail($id);

        // PG09で作成済みの「管理者用詳細画面」を表示
        // viewのパスは resources/views/admin/detail.blade.php なので 'admin.detail'
        return view('admin.detail', compact('attendance'));
    }

    /**
     * PG13: 修正申請の承認処理
     */
    public function approve(Request $request, $id)
    {
        // 1. 該当の勤怠データを取得
        $attendance = Attendance::findOrFail($id);

        // 2. ステータスを「承認済み」に更新
        $attendance->update([
            'status' => '承認済み'
        ]);

        // 3. 申請一覧画面へリダイレクト（メッセージ付き）
        return redirect()->route('admin.correction.list')->with('message', '承認が完了しました');
    }

}
