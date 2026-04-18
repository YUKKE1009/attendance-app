<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // 管理者なのでtrue
    }

    public function rules()
    {
        return [
            'clock_in'          => 'required',
            'clock_out'         => 'required',
            'rests.*.break_in'  => 'required', // 管理者修正時は入力済みを想定
            'rests.*.break_out' => 'required',
            'remarks'           => 'required', // 管理者側は name="remarks" なのでこちらに合わせます
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 時間を「分」に変換するヘルパー関数
            $toMinutes = function ($time) {
                if (!$time) return null;
                $parts = explode(':', $time);
                return (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
            };

            $clockInMin = $toMinutes($this->clock_in);
            $clockOutMin = $toMinutes($this->clock_out);

            // 1. 出勤・退勤の前後関係 (FN039-1)
            if ($clockInMin !== null && $clockOutMin !== null && $clockInMin >= $clockOutMin) {
                $validator->errors()->add('clock_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間のチェック (スタッフ側のロジックを継承)
            $rests = $this->rests ?? [];

            foreach ($rests as $rest) {
                $bInMin = $toMinutes($rest['break_in'] ?? null);
                $bOutMin = $toMinutes($rest['break_out'] ?? null);

                if ($bInMin === null || $bOutMin === null) continue;

                // 2. 休憩開始が出勤前、または退勤後 (FN039-2)
                if ($bInMin < $clockInMin || $bInMin > $clockOutMin) {
                    $validator->errors()->add('break_time', '休憩時間が不適切な値です');
                }

                // 3. 休憩終了が退勤後 (FN039-3)
                if ($bOutMin > $clockOutMin) {
                    $validator->errors()->add('break_time', '休憩時間もしくは退勤時間が不適切な値です');
                }

                // 休憩自体の前後関係チェック
                if ($bInMin >= $bOutMin) {
                    $validator->errors()->add('break_time', '休憩時間が不適切な値です');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください', // FN039-4
        ];
    }
}
