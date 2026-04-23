<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'target_date',
        'remark',
        'updated_clock_in',
        'updated_clock_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}