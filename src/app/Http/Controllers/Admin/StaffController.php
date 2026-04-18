<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.staff', compact('users'));
    }

    // ★ 追加：特定のスタッフの勤怠一覧を表示
    public function attendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // クエリパラメータ 'month' があればそれを使用、なければ今月
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $displayDate = Carbon::parse($month . '-01');

        // 前月と翌月の文字列 (YYYY-MM) を計算
        $prevMonth = $displayDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $displayDate->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.list', compact(
            'user',
            'attendances',
            'month',
            'displayDate',
            'prevMonth',
            'nextMonth'
        ));
    }
}