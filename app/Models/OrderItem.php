<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'wholesale_price',
        'sale_price',
        'profit_per_item',
    ];

    protected $casts = [
        'wholesale_price' => 'integer',
        'sale_price' => 'integer',
        'profit_per_item' => 'integer',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalSaleAttribute(): int
    {
        return $this->sale_price * $this->quantity;
    }

    public function getTotalProfitAttribute(): int
    {
        return $this->profit_per_item * $this->quantity;
    }
}
