<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'clock_in', 'clock_out', 'status'];

    // リレーション：出勤は1人のユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション：1つの出勤にはたくさんの休憩がある
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }
}