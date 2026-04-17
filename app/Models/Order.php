<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'reseller_id',
        'delivery_agent_id',
        'customer_name',
        'customer_phone',
        'customer_city',
        'customer_address',
        'total_sale_price',
        'total_wholesale_price',
        'delivery_fee',
        'zone_delivery_fee',
        'reseller_profit',
        'platform_profit',
        'status',
        'payment_method',
        'payment_status',
        'notes',
        'delivered_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_sale_price' => 'integer',
        'total_wholesale_price' => 'integer',
        'delivery_fee' => 'integer',
        'zone_delivery_fee' => 'integer',
        'reseller_profit' => 'integer',
        'platform_profit' => 'integer',
    ];

    // Status Labels (Arabic)
    public const STATUS_LABELS = [
        'new'               => 'جديد',
        'confirmed'         => 'مؤكد',
        'preparing'         => 'قيد التجهيز',
        'out_for_delivery'  => 'قيد التوصيل',
        'delivered'         => 'تم التسليم',
        'rejected'          => 'مرفوض',
        'returned'          => 'مرتجع',
    ];

    public const STATUS_COLORS = [
        'new'               => 'primary',
        'confirmed'         => 'info',
        'preparing'         => 'warning',
        'out_for_delivery'  => 'warning',
        'delivered'         => 'success',
        'rejected'          => 'danger',
        'returned'          => 'secondary',
    ];

    // --- Relationships ---
    public function reseller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function deliveryAgent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryAgent::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // --- Helpers ---
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return sprintf('KT-%d-%05d', $year, $last + 1);
    }

    // --- Scopes ---
    public function scopeForReseller($query, int $resellerId)
    {
        return $query->where('reseller_id', $resellerId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
