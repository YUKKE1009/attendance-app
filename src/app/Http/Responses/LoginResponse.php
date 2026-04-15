<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // 管理者なら管理者一覧へ、そうでなければ一般の勤怠一覧へ
        $redirect = Auth::guard('admin')->check()
            ? route('admin.attendance.list')
            : route('attendance.index');

        return redirect()->intended($redirect);
    }
}
