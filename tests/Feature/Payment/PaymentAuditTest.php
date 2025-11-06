<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Services\PaymentAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PaymentAuditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $order;
    protected $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total' => 100.00,
            'reference' => 'TEST-ORDER-001'
        ]);
        
        $this->auditService = app(PaymentAuditService::class);
    }

    public function test_payment_attempt_logging()
    {
        $auditLog = $this->auditService->logPaymentAttempt(
            $this->order, 
            'stripe',
            ['card_number' => '****1234', 'amount' => 100.00]
        );

        $this->assertInstanceOf(PaymentAuditLog::class, $auditLog);
        $this->assertEquals($this->order->id, $auditLog->order_id);
        $this->assertEquals($this->user->id, $auditLog->user_id);
        $this->assertEquals('stripe', $auditLog->gateway);
        $this->assertEquals('payment_attempt', $auditLog->event_type);
        $this->assertEquals('pending', $auditLog->status);
        $this->assertEquals(100.00, $auditLog->amount);
    }

    public function test_payment_success_logging()
    {
        $auditLog = $this->auditService->logPaymentSuccess(
            $this->order,
            'stripe',
            'pi_test_123',
            ['status' => 'succeeded']
        );

        $this->assertEquals('payment_success', $auditLog->event_type);
        $this->assertEquals('completed', $auditLog->status);
        $this->assertEquals('pi_test_123', $auditLog->transaction_id);
        $this->assertNotNull($auditLog->response_data);
    }

    public function test_payment_failure_logging()
    {
        $auditLog = $this->auditService->logPaymentFailure(
            $this->order,
            'stripe',
            'Your card was declined',
            ['error_code' => 'card_declined']
        );

        $this->assertEquals('payment_failed', $auditLog->event_type);
        $this->assertEquals('failed', $auditLog->status);
        $this->assertEquals('Your card was declined', $auditLog->error_message);
        $this->assertNotNull($auditLog->response_data);
    }

    public function test_refund_logging()
    {
        $auditLog = $this->auditService->logRefund(
            $this->order,
            'stripe',
            're_test_123',
            50.00,
            'Customer requested refund'
        );

        $this->assertEquals('refund', $auditLog->event_type);
        $this->assertEquals('completed', $auditLog->status);
        $this->assertEquals('re_test_123', $auditLog->transaction_id);
        $this->assertEquals(50.00, $auditLog->amount);
        $this->assertEquals('Customer requested refund', $auditLog->error_message);
    }

    public function test_chargeback_logging()
    {
        $auditLog = $this->auditService->logChargeback(
            $this->order,
            'stripe',
            'ch_test_123',
            100.00,
            'Fraudulent transaction'
        );

        $this->assertEquals('chargeback', $auditLog->event_type);
        $this->assertEquals('disputed', $auditLog->status);
        $this->assertEquals('ch_test_123', $auditLog->transaction_id);
        $this->assertEquals(100.00, $auditLog->amount);
        $this->assertEquals('Fraudulent transaction', $auditLog->error_message);
    }

    public function test_suspicious_activity_detection()
    {
        // Simulate multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $this->auditService->logPaymentFailure(
                $this->order,
                'stripe',
                'Card declined',
                []
            );
        }

        // Check if suspicious activity was logged
        $suspiciousLog = PaymentAuditLog::where('event_type', 'multiple_attempts')
            ->where('user_id', $this->user->id)
            ->first();

        $this->assertNotNull($suspiciousLog);
        $this->assertEquals('suspicious', $suspiciousLog->status);
    }

    public function test_card_testing_detection()
    {
        $ipAddress = '192.168.1.100';
        
        // Create multiple payment attempts from same IP
        for ($i = 0; $i < 12; $i++) {
            PaymentAuditLog::create([
                'gateway' => 'stripe',
                'event_type' => 'payment_attempt',
                'status' => 'pending',
                'ip_address' => $ipAddress,
                'created_at' => now()
            ]);
        }

        $isCardTesting = $this->auditService->detectCardTesting($ipAddress);
        
        $this->assertTrue($isCardTesting);
        
        // Check if card testing was logged
        $cardTestingLog = PaymentAuditLog::where('event_type', 'card_testing')
            ->where('ip_address', $ipAddress)
            ->first();

        $this->assertNotNull($cardTestingLog);
        $this->assertEquals('suspicious', $cardTestingLog->status);
    }

    public function test_payment_statistics()
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Create test data
        PaymentAuditLog::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'gateway' => 'stripe',
            'event_type' => 'payment_attempt',
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        PaymentAuditLog::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'gateway' => 'stripe',
            'event_type' => 'payment_success',
            'amount' => 100.00,
            'status' => 'completed'
        ]);

        $stats = $this->auditService->getPaymentStats($startDate, $endDate);

        $this->assertEquals(1, $stats['total_attempts']);
        $this->assertEquals(1, $stats['successful_payments']);
        $this->assertEquals(0, $stats['failed_payments']);
        $this->assertEquals(100.00, $stats['total_revenue']);
        $this->assertEquals(100.0, $stats['success_rate']);
    }

    public function test_gateway_performance_stats()
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Create test data for different gateways
        PaymentAuditLog::create([
            'gateway' => 'stripe',
            'event_type' => 'payment_success',
            'amount' => 100.00,
            'status' => 'completed'
        ]);

        PaymentAuditLog::create([
            'gateway' => 'paypal',
            'event_type' => 'payment_failed',
            'amount' => 50.00,
            'status' => 'failed'
        ]);

        $stats = $this->auditService->getGatewayStats($startDate, $endDate);

        $this->assertEquals(1, $stats['stripe']['successful']);
        $this->assertEquals(0, $stats['stripe']['failed']);
        $this->assertEquals(100.0, $stats['stripe']['success_rate']);

        $this->assertEquals(0, $stats['paypal']['successful']);
        $this->assertEquals(1, $stats['paypal']['failed']);
        $this->assertEquals(0.0, $stats['paypal']['success_rate']);
    }

    public function test_recent_suspicious_activities()
    {
        // Create suspicious activity
        PaymentAuditLog::create([
            'user_id' => $this->user->id,
            'gateway' => 'stripe',
            'event_type' => 'multiple_attempts',
            'status' => 'suspicious',
            'error_message' => 'Multiple failed attempts detected'
        ]);

        $activities = $this->auditService->getRecentSuspiciousActivities(10);

        $this->assertCount(1, $activities);
        $this->assertEquals('multiple_attempts', $activities[0]['event_type']);
        $this->assertEquals('suspicious', $activities[0]['status']);
    }

    public function test_failure_analysis()
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Create failure data
        PaymentAuditLog::create([
            'gateway' => 'stripe',
            'event_type' => 'payment_failed',
            'status' => 'failed',
            'error_message' => 'Insufficient funds',
            'created_at' => now()
        ]);

        PaymentAuditLog::create([
            'gateway' => 'stripe',
            'event_type' => 'payment_failed',
            'status' => 'failed',
            'error_message' => 'Card declined',
            'created_at' => now()
        ]);

        $analysis = $this->auditService->getFailureAnalysis($startDate, $endDate);

        $this->assertArrayHasKey('common_reasons', $analysis);
        $this->assertArrayHasKey('hourly_trends', $analysis);
        $this->assertCount(2, $analysis['common_reasons']);
    }

    public function test_audit_log_scopes()
    {
        // Create test data
        PaymentAuditLog::create([
            'gateway' => 'stripe',
            'event_type' => 'payment_success',
            'status' => 'completed'
        ]);

        PaymentAuditLog::create([
            'gateway' => 'paypal',
            'event_type' => 'payment_failed',
            'status' => 'failed'
        ]);

        PaymentAuditLog::create([
            'gateway' => 'stripe',
            'event_type' => 'chargeback',
            'status' => 'suspicious'
        ]);

        // Test scopes
        $stripePayments = PaymentAuditLog::forGateway('stripe')->count();
        $this->assertEquals(2, $stripePayments);

        $successfulPayments = PaymentAuditLog::successful()->count();
        $this->assertEquals(1, $successfulPayments);

        $failedPayments = PaymentAuditLog::failed()->count();
        $this->assertEquals(1, $failedPayments);

        $suspiciousActivities = PaymentAuditLog::suspicious()->count();
        $this->assertEquals(1, $suspiciousActivities);
    }
}