<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
    {
        // 管理者ガードでログアウト
        Auth::guard('admin')->logout();

        // セッションを無効化して再生成（セキュリティの定石）
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後は管理者のログイン画面へ
        return redirect('/admin/login');
    }
}
