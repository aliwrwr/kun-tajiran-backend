<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'password',
        'city',
        'role',
        'status',
        'otp_code',
        'otp_expires_at',
        'phone_verified',
        'fcm_token',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'phone_verified' => 'boolean',
        'otp_expires_at' => 'datetime',
    ];

    // --- JWT ---
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
        ];
    }

    // --- Relationships ---
    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'reseller_id');
    }

    public function deliveryAgent(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DeliveryAgent::class);
    }

    public function withdrawalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    // --- Helpers ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    public function isDelivery(): bool
    {
        return $this->role === 'delivery';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function generateOtp(): string
    {
        $otp = (string) random_int(100000, 999999);
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        return $otp;
    }
}
