<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        // 文字ではなく、resources/views/attendance/attendance.blade.php を呼ぶ
        return view('attendance.attendance');
    }
}
