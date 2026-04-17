<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'order_id',
        'type',
        'category',
        'amount',
        'balance_after',
        'description',
        'reference',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
