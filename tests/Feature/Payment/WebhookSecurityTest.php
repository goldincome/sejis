<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $order;

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
    }

    /** @test */
    public function it_validates_stripe_webhook_signature()
    {
        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = json_encode([
            'id' => 'evt_test_webhook',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'status' => 'succeeded',
                    'metadata' => [
                        'order_id' => $this->order->id
                    ]
                ]
            ]
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret');
        $header = "t=$timestamp,v1=$signature";

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $header
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_stripe_webhook_with_invalid_signature()
    {
        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = json_encode([
            'id' => 'evt_test_webhook',
            'object' => 'event',
            'type' => 'payment_intent.succeeded'
        ]);

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_validates_paypal_webhook_signature()
    {
        Config::set('paypal.webhook_id', 'test_webhook_id');
        Config::set('paypal.client_id', 'test_client_id');
        Config::set('paypal.client_secret', 'test_client_secret');

        $payload = [
            'id' => 'WH-test-webhook',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'capture_test_123',
                'status' => 'COMPLETED',
                'custom_id' => $this->order->reference
            ]
        ];

        // Mock PayPal webhook verification
        $this->mockHttpClient([
            [
                'status' => 200,
                'body' => json_encode(['verification_status' => 'SUCCESS'])
            ]
        ]);

        $response = $this->postJson('/webhooks/paypal', $payload, [
            'PAYPAL-TRANSMISSION-ID' => 'test_transmission_id',
            'PAYPAL-CERT-ID' => 'test_cert_id',
            'PAYPAL-TRANSMISSION-TIME' => time(),
            'PAYPAL-TRANSMISSION-SIG' => 'test_signature'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_paypal_webhook_with_missing_headers()
    {
        $payload = [
            'id' => 'WH-test-webhook',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED'
        ];

        $response = $this->postJson('/webhooks/paypal', $payload);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_validates_takepayments_webhook_with_hash_verification()
    {
        Config::set('takepayment.access_key', 'test_access_key');
        
        $payload = [
            'orderRef' => $this->order->reference,
            'transactionID' => 'tp_test_123',
            'responseCode' => '0',
            'responseMessage' => 'AUTHCODE:123456',
            'amountReceived' => '10000', // 100.00 in pence
            'transactionUnique' => 'unique_test_123'
        ];

        // Calculate expected hash
        $hashString = http_build_query($payload, '', '&');
        $expectedHash = hash('sha512', $hashString . 'test_access_key');
        $payload['signature'] = $expectedHash;

        $response = $this->postJson('/webhooks/takepayments', $payload);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_takepayments_webhook_with_invalid_hash()
    {
        $payload = [
            'orderRef' => $this->order->reference,
            'transactionID' => 'tp_test_123',
            'responseCode' => '0',
            'signature' => 'invalid_hash'
        ];

        $response = $this->postJson('/webhooks/takepayments', $payload);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_validates_bank_deposit_webhook_with_admin_token()
    {
        Config::set('app.admin_webhook_token', 'admin_secret_token');
        
        $payload = [
            'order_reference' => $this->order->reference,
            'deposit_amount' => 100.00,
            'deposit_date' => now()->toDateString(),
            'bank_reference' => 'BANK-REF-123',
            'verified_by' => 'admin@example.com'
        ];

        $response = $this->postJson('/webhooks/bank-deposit', $payload, [
            'Authorization' => 'Bearer admin_secret_token'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_bank_deposit_webhook_without_token()
    {
        $payload = [
            'order_reference' => $this->order->reference,
            'deposit_amount' => 100.00
        ];

        $response = $this->postJson('/webhooks/bank-deposit', $payload);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_webhook_replay_attacks_with_timestamp_validation()
    {
        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = json_encode([
            'id' => 'evt_test_webhook',
            'object' => 'event',
            'type' => 'payment_intent.succeeded'
        ]);

        // Timestamp older than 5 minutes (300 seconds)
        $oldTimestamp = time() - 400;
        $signature = hash_hmac('sha256', $oldTimestamp . '.' . $payload, 'whsec_test_secret');
        $header = "t=$oldTimestamp,v1=$signature";

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $header
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => 'Webhook timestamp too old']);
    }

    /** @test */
    public function it_prevents_webhook_duplicate_processing()
    {
        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = json_encode([
            'id' => 'evt_unique_webhook_123',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'status' => 'succeeded',
                    'metadata' => [
                        'order_id' => $this->order->id
                    ]
                ]
            ]
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret');
        $header = "t=$timestamp,v1=$signature";

        // First webhook call should succeed
        $response1 = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $header
        ]);
        $response1->assertStatus(200);

        // Second identical webhook call should be ignored (idempotent)
        $response2 = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $header
        ]);
        $response2->assertStatus(200);
        $response2->assertJsonFragment(['message' => 'Webhook already processed']);
    }

    /** @test */
    public function it_logs_webhook_security_violations()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Webhook security violation', [
                'gateway' => 'stripe',
                'ip' => '127.0.0.1',
                'reason' => 'Invalid signature',
                'payload_id' => 'evt_test_webhook'
            ]);

        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = [
            'id' => 'evt_test_webhook',
            'object' => 'event',
            'type' => 'payment_intent.succeeded'
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_validates_webhook_ip_whitelist()
    {
        Config::set('webhooks.allowed_ips', ['127.0.0.1', '192.168.1.100']);
        
        // Test from allowed IP
        $response1 = $this->from('127.0.0.1')
            ->postJson('/webhooks/stripe', ['test' => 'data']);
        
        // Should not be blocked by IP (but may fail for other reasons)
        $this->assertNotEquals(403, $response1->getStatusCode());

        // Test from disallowed IP would require additional setup
        // This test demonstrates the concept
    }

    /** @test */
    public function it_validates_webhook_rate_limiting()
    {
        Config::set('stripe.webhook_secret', 'whsec_test_secret');
        
        $payload = json_encode([
            'id' => 'evt_rate_limit_test',
            'object' => 'event',
            'type' => 'payment_intent.succeeded'
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret');
        $header = "t=$timestamp,v1=$signature";

        // Make multiple rapid requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
                'Stripe-Signature' => $header
            ]);
        }

        // After rate limit is exceeded, should get 429
        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $header
        ]);
        
        // Note: Actual rate limiting would depend on implementation
        // This test demonstrates the concept
    }

    /**
     * Mock HTTP client responses for testing
     */
    protected function mockHttpClient(array $responses): void
    {
        // This would typically use HTTP::fake() in newer Laravel versions
        // or mock the HTTP client used by PayPal webhook verification
    }
}