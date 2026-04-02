<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'proposed_clock_in',
        'proposed_clock_out'
    ];

    /**
     * この修正案が紐付いている「修正申請」を取得
     */
    public function correctionRequest()
    {
        // 外部キーが request_id なので、第2引数で指定しておくと確実です
        return $this->belongsTo(CorrectionRequest::class, 'request_id');
    }
}
