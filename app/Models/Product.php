<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'images',
        'youtube_url',
        'wholesale_price',
        'suggested_price',
        'min_price',
        'delivery_fee',
        'stock_quantity',
        'sku',
        'weight',
        'is_active',
        'is_featured',
        'sales_count',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'wholesale_price' => 'integer',
        'suggested_price' => 'integer',
        'min_price' => 'integer',
        'delivery_fee' => 'integer',
    ];

    // --- Relationships ---
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // --- Computed ---
    public function getResellerProfitAttribute(): int
    {
        return $this->suggested_price - $this->wholesale_price;
    }

    public function getThumbnailAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function decrementStock(int $quantity = 1): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    public function incrementStock(int $quantity = 1): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    // --- Scopes ---
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('stock_quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
