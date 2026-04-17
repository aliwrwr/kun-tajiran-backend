<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get all settings as key=>value array
     */
    public static function getAllAsArray(): array
    {
        return self::pluck('value', 'key')->toArray();
    }

    /**
     * Get a single setting value with a default
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set or update a setting
     */
    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Set multiple settings at once
     */
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            self::setValue($key, $value);
        }
    }

    /**
     * Default settings
     */
    public static function defaults(): array
    {
        return [
            'sections_config' => json_encode([
                [
                    'key'       => 'banners',
                    'title'     => 'عروض حصرية',
                    'visible'   => true,
                    'auto_play' => true,
                    'duration'  => 4,
                ],
                [
                    'key'        => 'featured',
                    'title'      => '🔥 منتجات مميزة',
                    'visible'    => true,
                    'view_mode'  => 'horizontal',
                    'card_style' => 'standard',
                    'count'      => 6,
                ],
                [
                    'key'        => 'categories',
                    'title'      => '📦 الأقسام',
                    'visible'    => true,
                    'view_mode'  => 'horizontal',
                    'card_style' => 'chip',
                    'count'      => 8,
                ],
                [
                    'key'        => 'products',
                    'title'      => '🛍️ جميع المنتجات',
                    'visible'    => true,
                    'view_mode'  => 'grid',
                    'card_style' => 'standard',
                    'count'      => 10,
                ],
            ], JSON_UNESCAPED_UNICODE),
        ];
    }
}
