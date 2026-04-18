<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class CorrectionController extends Controller
{
    public function index(Request $request)
    {
        // タブの状態を取得（デフォルトは pending）
        $status = $request->query('status', 'pending');

        // 修正申請をユーザー情報と一緒に取得
        $query = CorrectionRequest::with('user');

        if ($status === 'approved') {
            // 2:承認済み
            $requests = $query->where('status', 2)->get();
        } else {
            // 1:承認待ち
            $requests = $query->where('status', 1)->get();
        }

        return view('admin.request', compact('requests', 'status'));
    }
}
