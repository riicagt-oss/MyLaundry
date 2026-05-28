<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'service_name',
        'price',
        'qty_or_weight',
        'unit',
        'subtotal',
        'estimation_name'
    ];

    protected $casts = [
        'price' => 'integer',
        'qty_or_weight' => 'float',
        'subtotal' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
