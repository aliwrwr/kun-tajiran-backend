<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeliveryOffer extends Model
{
    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'applies_to',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'discount_value'=> 'integer',
    ];

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'delivery_offer_sellers');
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function applyDiscount(int $baseFee): int
    {
        return match ($this->discount_type) {
            'free'       => 0,
            'fixed'      => max(0, $baseFee - $this->discount_value),
            'percentage' => (int) round($baseFee * (1 - $this->discount_value / 100)),
            default      => $baseFee,
        };
    }

    public function getDiscountLabelAttribute(): string
    {
        return match ($this->discount_type) {
            'free'       => 'توصيل مجاني',
            'fixed'      => "خصم {$this->discount_value} د.ع",
            'percentage' => "خصم {$this->discount_value}%",
            default      => '',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'معطّل';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'لم يبدأ بعد';
        if ($this->ends_at && $this->ends_at->isPast()) return 'منتهي';
        return 'نشط';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'نشط'         => 'success',
            'معطّل'        => 'secondary',
            'لم يبدأ بعد' => 'warning',
            'منتهي'       => 'danger',
            default       => 'secondary',
        };
    }
}
