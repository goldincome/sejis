<?php

namespace App\Models;

use App\Models\User;
use App\Models\BankDeposit;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany, Relations\HasOne};


class Order extends Model
{
    use HasFactory;
   protected $fillable = [
        'user_id',
        'order_no',
        'reference',
        'total',
        'sub_total',
        'tax',
        'payment_method',
        'payment_method_order_id',
        'payment_gateway',
        'transaction_id',
        'bank_deposit_id',
        'currency',
        'status',
        'failure_reason',
        'paid_at',
    ];
    
    protected $casts = [
        'status' => OrderStatusEnum::class,
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function bankDeposit(): HasOne
    {
        return $this->hasOne(BankDeposit::class);
    }
}
