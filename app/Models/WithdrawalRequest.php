<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'account_number',
        'account_name',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'processed_at' => 'datetime',
    ];

    public const METHOD_LABELS = [
        'zain_cash'     => 'زين كاش',
        'asia_hawala'   => 'آسيا حوالة',
        'bank_transfer' => 'تحويل بنكي',
        'office_pickup' => 'استلام من المكتب',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }
}
