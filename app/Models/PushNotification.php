<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'title', 'body', 'image_url', 'target_type', 'target_role',
        'target_user_id', 'click_action', 'data',
        'sent_count', 'failed_count', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'data'    => 'array',
        'sent_at' => 'datetime',
    ];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_type) {
            'all'      => 'الجميع',
            'role'     => match ($this->target_role) {
                'reseller' => 'البائعون',
                'delivery' => 'المناديب',
                default    => $this->target_role,
            },
            'user'     => 'مستخدم محدد',
            default    => $this->target_type,
        };
    }
}
