<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestUpdate extends Model
{
    use HasFactory;

    // 保存を許可するカラム一覧
    protected $fillable = [
        'request_id',
        'rest_id',
        'proposed_break_in',
        'proposed_break_out',
    ];

    /**
     * リレーション：この修正案が属する「修正申請」を取得
     */
    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class, 'request_id');
    }

    /**
     * リレーション：この修正案が対象としている「元の休憩データ」を取得
     */
    public function rest()
    {
        return $this->belongsTo(Rest::class, 'rest_id');
    }
}
