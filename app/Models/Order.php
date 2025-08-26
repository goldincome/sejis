<?php

namespace App\Models;

use App\Models\User;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany};


class Order extends Model
{
   protected $fillable = [
        'user_id',
        'order_no',
        'total',
        'sub_total',
        'tax',
        'payment_method',
        'payment_method_order_id',
        'currency',
        'status',
    ];
    
    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
