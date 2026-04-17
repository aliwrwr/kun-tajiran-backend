<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'province_name',
        'province_name_en',
        'base_fee',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_fee'  => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate final fee after applying an active offer for a given reseller.
     */
    public static function getFeeForReseller(string $province, int $resellerId): int
    {
        $zone = self::active()
            ->where('province_name', $province)
            ->first();

        if (!$zone) {
            return 0;
        }

        $baseFee = $zone->base_fee;

        // Find best active offer for this reseller
        $offer = DeliveryOffer::active()
            ->where(function ($q) use ($resellerId) {
                $q->where('applies_to', 'all')
                  ->orWhereHas('sellers', fn ($s) => $s->where('user_id', $resellerId));
            })
            ->orderByRaw("CASE discount_type WHEN 'free' THEN 0 ELSE 1 END")
            ->orderByDesc('discount_value')
            ->first();

        if (!$offer) {
            return $baseFee;
        }

        return $offer->applyDiscount($baseFee);
    }
}
