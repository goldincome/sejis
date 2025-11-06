<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'transaction_id',
        'event_type',
        'amount',
        'currency',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'ip_address',
        'user_agent',
        'processed_at',
        'webhook_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'array',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the order associated with this audit log
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user associated with this audit log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by gateway
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by event type
     */
    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['completed', 'success', 'paid']);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'error', 'declined', 'cancelled']);
    }

    /**
     * Scope for suspicious activities
     */
    public function scopeSuspicious($query)
    {
        return $query->where(function ($q) {
            $q->where('event_type', 'multiple_attempts')
              ->orWhere('event_type', 'card_testing')
              ->orWhere('event_type', 'chargeback')
              ->orWhere('event_type', 'fraud_detected');
        });
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get total amount for the query
     */
    public function scopeGetTotalAmount($query)
    {
        return $query->sum('amount');
    }

    /**
     * Create audit log entry
     */
    public static function logPaymentEvent(array $data): self
    {
        return self::create([
            'order_id' => $data['order_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'gateway' => $data['gateway'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'event_type' => $data['event_type'],
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'GBP',
            'status' => $data['status'],
            'request_data' => $data['request_data'] ?? null,
            'response_data' => $data['response_data'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'processed_at' => $data['processed_at'] ?? now(),
            'webhook_id' => $data['webhook_id'] ?? null
        ]);
    }
}