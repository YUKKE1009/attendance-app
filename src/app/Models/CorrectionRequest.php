<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $fillable = ['attendance_id', 'user_id', 'status', 'reason', 'proposed_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 修正案（出退勤）との紐付け
    public function attendanceUpdate()
    {
        return $this->hasOne(AttendanceUpdate::class, 'request_id');
    }

    // 修正案（休憩）との紐付け
    // 1回の申請で複数の休憩を直す可能性があるなら hasMany、1つなら hasOne です
    public function restUpdates()
    {
        return $this->hasMany(RestUpdate::class, 'request_id');
    }
}