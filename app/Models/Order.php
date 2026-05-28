<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'phone',
        'total_price',
        'status',
        'total_weight',
        'estimation_time',
        'payment_method',
        'cash_received',
        'cash_change',
        'address',
        'latitude',
        'longitude',
        'delivery_type',
        'delivery_fee',
        'delivery_distance',
    ];

    /**
     * Casting format kolom agar dibaca sebagai objek tanggal oleh Laravel
     */
    protected $casts = [
        'estimation_time' => 'datetime',
        'total_price' => 'integer',
        'total_weight' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Accessor untuk mendapatkan service_name dari items
     * Menghindari error di view karena kolom service_name dihapus dari tabel orders
     */
    public function getServiceNameAttribute()
    {
        if ($this->items->isEmpty()) {
            return 'Layanan Umum';
        }

        return $this->items->map(function ($item) {
            $qty = $item->unit === 'PCS' ? round($item->qty_or_weight) . ' PCS' : number_format($item->qty_or_weight, 3, '.', '') . ' KG';
            return $item->service_name . ' (' . $qty . ')||' . $item->subtotal;
        })->implode("\n");
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

        static::creating(function ($order) {
            if (Auth::check()) {
                $user = Auth::user();
                $order->user_id = $user->getOwnerId();
            }
        });
    }
}