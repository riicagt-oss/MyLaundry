<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'owner_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi: Seorang Owner punya banyak Staff/Driver
     */
    public function staffs(): HasMany
    {
        return $this->hasMany(User::class, 'owner_id');
    }

    /**
     * Relasi: Seorang Staff/Driver punya satu Owner (Bos)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relasi: Seorang Owner punya satu Toko
     */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    /**
     * Helper: Dapatkan Owner ID (baik user ini owner atau staf/driver)
     */
    public function getOwnerId(): int
    {
        return ($this->role === 'staf' || $this->role === 'driver') ? $this->owner_id : $this->id;
    }

    /**
     * Helper: Dapatkan data Shop milik Owner
     */
    public function getOwnerShop(): ?Shop
    {
        $ownerId = $this->getOwnerId();
        return Shop::where('user_id', $ownerId)->first();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }
}
