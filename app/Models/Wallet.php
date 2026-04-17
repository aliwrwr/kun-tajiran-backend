<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance' => 'integer',
        'pending_balance' => 'integer',
        'total_earned' => 'integer',
        'total_withdrawn' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function credit(int $amount, string $category, string $description, ?int $orderId = null): Transaction
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);

        return $this->transactions()->create([
            'type' => 'credit',
            'category' => $category,
            'amount' => $amount,
            'balance_after' => $this->fresh()->balance,
            'description' => $description,
            'order_id' => $orderId,
        ]);
    }

    public function debit(int $amount, string $category, string $description, ?int $orderId = null): Transaction
    {
        if ($this->balance < $amount) {
            throw new \Exception('رصيد غير كافٍ');
        }

        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);

        return $this->transactions()->create([
            'type' => 'debit',
            'category' => $category,
            'amount' => $amount,
            'balance_after' => $this->fresh()->balance,
            'description' => $description,
            'order_id' => $orderId,
        ]);
    }
}
