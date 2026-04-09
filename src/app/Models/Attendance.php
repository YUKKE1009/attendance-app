<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // 保存を許可するカラムの指定
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'note'
    ];

    /**
     * リレーション：出勤は1人のユーザーに属する
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション：1つの出勤には複数の休憩(rests)が紐づく
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }
}
