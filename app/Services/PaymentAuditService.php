<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentAuditService
{
    /**
     * Log payment attempt
     */
    public function logPaymentAttempt(Order $order, string $gateway, array $requestData = []): PaymentAuditLog
    {
        return PaymentAuditLog::logPaymentEvent([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'event_type' => 'payment_attempt',
            'amount' => $order->total,
            'status' => 'pending',
            'request_data' => $requestData
        ]);
    }

    /**
     * Log successful payment
     */
    public function logPaymentSuccess(Order $order, string $gateway, string $transactionId, array $responseData = []): PaymentAuditLog
    {
        $auditLog = PaymentAuditLog::logPaymentEvent([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'event_type' => 'payment_success',
            'amount' => $order->total,
            'status' => 'completed',
            'response_data' => $responseData
        ]);

        // Log success message
        Log::channel('payment')->info('Payment successful', [
            'order_id' => $order->id,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'amount' => $order->total
        ]);

        return $auditLog;
    }

    /**
     * Log failed payment
     */
    public function logPaymentFailure(Order $order, string $gateway, string $errorMessage, array $responseData = []): PaymentAuditLog
    {
        $auditLog = PaymentAuditLog::logPaymentEvent([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'event_type' => 'payment_failed',
            'amount' => $order->total,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_data' => $responseData
        ]);

        // Log failure with details
        Log::channel('payment')->warning('Payment failed', [
            'order_id' => $order->id,
            'gateway' => $gateway,
            'error' => $errorMessage,
            'amount' => $order->total
        ]);

        // Check for suspicious activity
        $this->checkSuspiciousActivity($order->user_id, $gateway);

        return $auditLog;
    }

    /**
     * Log refund
     */
    public function logRefund(Order $order, string $gateway, string $refundId, float $amount, string $reason = ''): PaymentAuditLog
    {
        return PaymentAuditLog::logPaymentEvent([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'transaction_id' => $refundId,
            'event_type' => 'refund',
            'amount' => $amount,
            'status' => 'completed',
            'error_message' => $reason
        ]);
    }

    /**
     * Log chargeback
     */
    public function logChargeback(Order $order, string $gateway, string $chargebackId, float $amount, string $reason = ''): PaymentAuditLog
    {
        $auditLog = PaymentAuditLog::logPaymentEvent([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'transaction_id' => $chargebackId,
            'event_type' => 'chargeback',
            'amount' => $amount,
            'status' => 'disputed',
            'error_message' => $reason
        ]);

        // Alert administrators about chargeback
        Log::channel('security')->critical('Chargeback received', [
            'order_id' => $order->id,
            'gateway' => $gateway,
            'chargeback_id' => $chargebackId,
            'amount' => $amount,
            'reason' => $reason
        ]);

        return $auditLog;
    }

    /**
     * Log webhook processing
     */
    public function logWebhookProcessing(string $gateway, string $eventType, array $payload, string $status = 'received'): WebhookLog
    {
        return WebhookLog::create([
            'gateway' => $gateway,
            'event_id' => $payload['id'] ?? null,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => $status,
            'processed_at' => now()
        ]);
    }

    /**
     * Check for suspicious payment activity
     */
    protected function checkSuspiciousActivity(int $userId, string $gateway): void
    {
        $cacheKey = "payment_attempts:{$userId}:{$gateway}";
        $attempts = Cache::get($cacheKey, 0);
        $attempts++;
        
        // Store attempts for 1 hour
        Cache::put($cacheKey, $attempts, 3600);

        // Flag suspicious activity after 5 failed attempts in 1 hour
        if ($attempts >= 5) {
            PaymentAuditLog::logPaymentEvent([
                'user_id' => $userId,
                'gateway' => $gateway,
                'event_type' => 'multiple_attempts',
                'status' => 'suspicious',
                'error_message' => "Multiple payment failures detected: {$attempts} attempts"
            ]);

            Log::channel('security')->warning('Suspicious payment activity detected', [
                'user_id' => $userId,
                'gateway' => $gateway,
                'attempts' => $attempts
            ]);
        }
    }

    /**
     * Detect card testing patterns
     */
    public function detectCardTesting(string $ipAddress): bool
    {
        $cacheKey = "card_testing:{$ipAddress}";
        $attempts = Cache::get($cacheKey, 0);
        
        // Check for rapid successive payment attempts from same IP
        $recentAttempts = PaymentAuditLog::where('ip_address', $ipAddress)
            ->where('event_type', 'payment_attempt')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentAttempts >= 10) {
            PaymentAuditLog::logPaymentEvent([
                'gateway' => 'system',
                'event_type' => 'card_testing',
                'status' => 'suspicious',
                'ip_address' => $ipAddress,
                'error_message' => "Potential card testing detected: {$recentAttempts} attempts in 10 minutes"
            ]);

            Log::channel('security')->critical('Card testing detected', [
                'ip_address' => $ipAddress,
                'attempts' => $recentAttempts
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get payment statistics for admin dashboard
     */
    public function getPaymentStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $stats = [
            'total_attempts' => PaymentAuditLog::forEvent('payment_attempt')
                ->dateRange($startDate, $endDate)
                ->count(),
            
            'successful_payments' => PaymentAuditLog::forEvent('payment_success')
                ->dateRange($startDate, $endDate)
                ->count(),
            
            'failed_payments' => PaymentAuditLog::forEvent('payment_failed')
                ->dateRange($startDate, $endDate)
                ->count(),
            
            'total_revenue' => PaymentAuditLog::forEvent('payment_success')
                ->dateRange($startDate, $endDate)
                ->sum('amount'),
            
            'refunds_count' => PaymentAuditLog::forEvent('refund')
                ->dateRange($startDate, $endDate)
                ->count(),
            
            'refunds_amount' => PaymentAuditLog::forEvent('refund')
                ->dateRange($startDate, $endDate)
                ->sum('amount'),
            
            'chargebacks_count' => PaymentAuditLog::forEvent('chargeback')
                ->dateRange($startDate, $endDate)
                ->count(),
            
            'chargebacks_amount' => PaymentAuditLog::forEvent('chargeback')
                ->dateRange($startDate, $endDate)
                ->sum('amount'),
            
            'suspicious_activities' => PaymentAuditLog::suspicious()
                ->dateRange($startDate, $endDate)
                ->count()
        ];

        // Calculate success rate
        $stats['success_rate'] = $stats['total_attempts'] > 0 
            ? round(($stats['successful_payments'] / $stats['total_attempts']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Get gateway performance stats
     */
    public function getGatewayStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $gateways = ['stripe', 'paypal', 'takepayments', 'bank_deposit'];
        $stats = [];

        foreach ($gateways as $gateway) {
            $successful = PaymentAuditLog::forGateway($gateway)
                ->forEvent('payment_success')
                ->dateRange($startDate, $endDate)
                ->count();
            
            $failed = PaymentAuditLog::forGateway($gateway)
                ->forEvent('payment_failed')
                ->dateRange($startDate, $endDate)
                ->count();
            
            $total = $successful + $failed;
            
            $stats[$gateway] = [
                'successful' => $successful,
                'failed' => $failed,
                'total' => $total,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                'revenue' => PaymentAuditLog::forGateway($gateway)
                    ->forEvent('payment_success')
                    ->dateRange($startDate, $endDate)
                    ->sum('amount')
            ];
        }

        return $stats;
    }

    /**
     * Get recent suspicious activities
     */
    public function getRecentSuspiciousActivities(int $limit = 50): array
    {
        return PaymentAuditLog::suspicious()
            ->recent(72) // Last 72 hours
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with(['order', 'user'])
            ->get()
            ->toArray();
    }

    /**
     * Get payment failure analysis
     */
    public function getFailureAnalysis(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        // Common failure reasons
        $failureReasons = PaymentAuditLog::forEvent('payment_failed')
            ->dateRange($startDate, $endDate)
            ->whereNotNull('error_message')
            ->selectRaw('error_message, COUNT(*) as count')
            ->groupBy('error_message')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // Failure trends by hour
        $hourlyFailures = PaymentAuditLog::forEvent('payment_failed')
            ->dateRange($startDate, $endDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();

        return [
            'common_reasons' => $failureReasons,
            'hourly_trends' => $hourlyFailures
        ];
    }
}