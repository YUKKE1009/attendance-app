<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'  => 'required',
            'clock_out' => 'required',
            'rests.*.break_in'  => 'nullable',
            'rests.*.break_out' => 'nullable',
            'remarks'      => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 時間を「分」に変換するヘルパー関数（クロージャ）
            $toMinutes = function ($time) {
                if (!$time) return null;
                // "25:30" を 25 と 30 に分ける
                $parts = explode(':', $time);
                return (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
            };

            $clockInMin = $toMinutes($this->clock_in);
            $clockOutMin = $toMinutes($this->clock_out);

            // 1. 出勤・退勤の前後関係 (FN029-1)
            if ($clockInMin !== null && $clockOutMin !== null && $clockInMin >= $clockOutMin) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間のチェック
            $rests = $this->rests ?? [];
            if ($this->new_rest_in && $this->new_rest_out) {
                $rests['new'] = ['break_in' => $this->new_rest_in, 'break_out' => $this->new_rest_out];
            }

            foreach ($rests as $rest) {
                $bInMin = $toMinutes($rest['break_in'] ?? null);
                $bOutMin = $toMinutes($rest['break_out'] ?? null);

                if ($bInMin === null || $bOutMin === null) continue;

                // 2. 休憩開始が出勤前、または退勤後 (FN029-2)
                if ($bInMin < $clockInMin || $bInMin > $clockOutMin) {
                    $validator->errors()->add('rests', '休憩時間が不適切な値です');
                }

                // 3. 休憩終了が退勤後 (FN029-3)
                if ($bOutMin > $clockOutMin) {
                    $validator->errors()->add('rests', '休憩時間もしくは退勤時間が不適切な値です');
                }

                // 休憩自体の前後関係チェック
                if ($bInMin >= $bOutMin) {
                    $validator->errors()->add('rests', '休憩時間が不適切な値です');
                }
            }
        });
    }
    
    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください', // FN029-4
        ];
    }
}
