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
        'rests.*.break_in'  => 'required',
        'rests.*.break_out' => 'required',
        'note'      => 'required', // FN029-4
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $clockIn = $this->clock_in;
        $clockOut = $this->clock_out;

        // 1. 出勤・退勤の前後関係 (FN029-1)
        if ($clockIn && $clockOut && $clockIn >= $clockOut) {
            $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
        }

        // 休憩時間のループチェック
        $rests = $this->rests ?? [];
        // 新規追加分もチェック対象に含める
        if ($this->new_rest_in && $this->new_rest_out) {
            $rests['new'] = ['break_in' => $this->new_rest_in, 'break_out' => $this->new_rest_out];
        }

        foreach ($rests as $rest) {
            $bIn = $rest['break_in'];
            $bOut = $rest['break_out'];

            if (!$bIn || !$bOut) continue;

            // 2. 休憩開始が出勤前、または退勤後 (FN029-2)
            if ($bIn < $clockIn || $bIn > $clockOut) {
                $validator->errors()->add('rests', '休憩時間が不適切な値です');
            }

            // 3. 休憩終了が退勤後 (FN029-3)
            if ($bOut > $clockOut) {
                $validator->errors()->add('rests', '休憩時間もしくは退勤時間が不適切な値です');
            }
            
            // 休憩自体の前後関係チェック（一般的バリデーション）
            if ($bIn >= $bOut) {
                $validator->errors()->add('rests', '休憩時間が不適切な値です');
            }
        }
    });
}

public function messages()
{
    return [
        'note.required' => '備考を記入してください', // FN029-4
    ];
}
}
