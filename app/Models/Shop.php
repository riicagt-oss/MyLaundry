<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'device_id',
        'latitude',
        'longitude',
        'delivery_fee_per_km',
        'free_delivery_km',
        'is_delivery_active',
        'is_estimation_active',
        'express_extra_price',
        'kilat_extra_price',
    ];

    protected $casts = [
        'is_delivery_active' => 'boolean',
        'is_estimation_active' => 'boolean',
        'free_delivery_km' => 'integer',
        'express_extra_price' => 'integer',
        'kilat_extra_price' => 'integer',
    ];

    /**
     * Relasi: Shop milik seorang Owner
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
