<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    protected $fillable = ['attendance_id', 'break_in', 'break_out'];

    // リレーション：休憩は1つの出勤に属する
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
