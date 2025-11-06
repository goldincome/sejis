<?php

namespace App\Models;

use App\Models\Order;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = [
        'name',
        'order_id',
        'product_id',
        'quantity',
        'price',
        'product_type',
        'sub_total',
        'booked_date',
        'start_date',
        'end_date',
        'booked_durations',
        'ref_no'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'booked_date' => 'datetime',
        'booked_durations' => 'array', // Casts the JSON column to an array
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
