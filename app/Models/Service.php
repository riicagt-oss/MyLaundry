<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'unit',
        'is_active',
    ];

    /**
     * Relasi: Service punya banyak Order
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Logic Booted untuk Multi-Tenancy
     */
    protected static function booted()
    {
        static::addGlobalScope('owner', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                $ownerId = $user->getOwnerId();
                $builder->where('user_id', $ownerId);
            }
        });

        static::creating(function ($service) {
            if (Auth::check()) {
                $user = Auth::user();
                $service->user_id = $user->getOwnerId();
            }
        });
    }
}
