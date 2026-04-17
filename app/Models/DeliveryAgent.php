<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'license_number',
        'city',
        'status',
        'delivered_count',
        'total_earnings',
    ];

    protected $casts = [
        'delivered_count' => 'integer',
        'total_earnings' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class)->whereIn('status', ['out_for_delivery']);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
