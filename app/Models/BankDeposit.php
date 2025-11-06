<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bank_reference',
        'deposit_amount',
        'deposit_date',
        'verified_by',
        'verification_notes',
        'status',
        'verified_at'
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'deposit_date' => 'date',
        'verified_at' => 'datetime'
    ];

    /**
     * Get the order associated with this bank deposit
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for confirmed deposits
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    /**
     * Check if deposit is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if deposit is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }
}