<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'clock_in', 'clock_out', 'status', 'remarks'];

    // リレーション：ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション：休憩（1つの勤怠に複数の休憩がある可能性を考慮）
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    // 1. 休憩合計時間の計算
    public function getTotalBreakAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->rests as $rest) {
            if ($rest->break_in && $rest->break_out) {
                $in = Carbon::parse($rest->break_in);
                $out = Carbon::parse($rest->break_out);
                $totalMinutes += $in->diffInMinutes($out);
            }
        }
        // 分を「H:i」形式に変換
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%01d:%02d', $hours, $minutes);
    }

    // 2. 勤務合計時間の計算（（退勤 - 出勤）- 休憩）
    public function getTotalWorkAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $in = Carbon::parse($this->clock_in);
            $out = Carbon::parse($this->clock_out);

            // 出勤〜退勤の総時間（分）
            $workingMinutes = $in->diffInMinutes($out);

            // 休憩時間の合計（分）を引く
            $breakMinutes = 0;
            foreach ($this->rests as $rest) {
                if ($rest->break_in && $rest->break_out) {
                    $breakMinutes += Carbon::parse($rest->break_in)->diffInMinutes(Carbon::parse($rest->break_out));
                }
            }

            $netWorkingMinutes = $workingMinutes - $breakMinutes;

            $hours = floor($netWorkingMinutes / 60);
            $minutes = $netWorkingMinutes % 60;
            return sprintf('%01d:%02d', $hours, $minutes);
        }
        return '';
    }
}
