<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PaymentGateways\PaypalGateway;
use App\Exceptions\PaymentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PayPalPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private CartService $cartService;
    private PaypalGateway $paypalGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'paypal@test.com',
            'user_type' => 'customer'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Commercial Kitchen Rental',
            'price' => 200.00,
            'is_active' => true
        ]);
        
        $this->cartService = app(CartService::class);
        $this->paypalGateway = app(PaypalGateway::class);
        
        // Set test PayPal configuration
        Config::set('paypal.mode', 'sandbox');
        Config::set('paypal.sandbox.client_id', 'test_client_id');
        Config::set('paypal.sandbox.client_secret', 'test_client_secret');
    }

    /** @test */
    public function paypal_order_creation_redirects_to_approval()
    {
        Http::fake([
            'api.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAY-TEST-ORDER-123',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAY-TEST-ORDER-123',
                        'rel' => 'approve',
                        'method' => 'GET'
                    ],
                    [
                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/PAY-TEST-ORDER-123',
                        'rel' => 'self',
                        'method' => 'GET'
                    ]
                ]
            ], 201),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, [
            'quantity' => 1,
            'booked_date' => now()->addDays(3)->format('Y-m-d'),
            'booking_time' => '0900-1200'
        ]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'paypal'
        ]);

        // Should redirect to PayPal approval URL
        $response->assertRedirect();
        $this->assertStringContainsString('paypal.com', $response->getTargetUrl());

        // Verify order was created in pending state
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'paypal',
            'status' => 'pending',
            'total' => 200.00
        ]);

        // Verify PayPal API was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.sandbox.paypal.com/v2/checkout/orders') &&
                   $request->method() === 'POST';
        });
    }

    /** @test */
    public function paypal_return_successful_payment_completion()
    {
        // Mock PayPal capture response
        Http::fake([
            'api.sandbox.paypal.com/v2/checkout/orders/PAY-TEST-ORDER-123/capture' => Http::response([
                'id' => 'PAY-TEST-ORDER-123',
                'status' => 'COMPLETED',
                'payment_source' => [
                    'paypal' => [
                        'email_address' => 'customer@example.com',
                        'account_id' => 'PAYPAL123456789'
                    ]
                ],
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'id' => 'CAPTURE123456789',
                                    'status' => 'COMPLETED',
                                    'amount' => [
                                        'currency_code' => 'GBP',
                                        'value' => '200.00'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        // Create pending order
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-001',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'sub_total' => 200.00,
            'tax' => 40.00,
            'total' => 240.00,
            'paypal_order_id' => 'PAY-TEST-ORDER-123'
        ]);

        $response = $this->get(route('user.payment.paypal.return', [
            'token' => 'PAY-TEST-ORDER-123',
            'PayerID' => 'PAYER123456789',
            'order_id' => $order->id
        ]));

        $response->assertRedirect(route('user.checkout.success', $order));

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->paypal_capture_id);

        // Verify PayPal capture was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/capture') &&
                   $request->method() === 'POST';
        });
    }

    /** @test */
    public function paypal_return_with_cancelled_payment()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-CANCEL',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'total' => 240.00,
            'paypal_order_id' => 'PAY-CANCELLED-ORDER'
        ]);

        $response = $this->get(route('user.payment.paypal.cancel', [
            'token' => 'PAY-CANCELLED-ORDER',
            'order_id' => $order->id
        ]));

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');

        // Verify order status updated to cancelled
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function paypal_webhook_payment_completed()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-WEBHOOK',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'total' => 200.00,
            'paypal_order_id' => 'PAY-WEBHOOK-TEST'
        ]);

        $webhookPayload = [
            'id' => 'WH-TEST-EVENT-123',
            'event_type' => 'CHECKOUT.ORDER.COMPLETED',
            'resource' => [
                'id' => 'PAY-WEBHOOK-TEST',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'id' => 'WEBHOOK-CAPTURE-123',
                                    'status' => 'COMPLETED',
                                    'amount' => [
                                        'currency_code' => 'GBP',
                                        'value' => '200.00'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post(route('webhooks.paypal'), $webhookPayload, [
            'HTTP_PAYPAL_TRANSMISSION_ID' => 'test-transmission-id',
            'HTTP_PAYPAL_CERT_ID' => 'test-cert-id',
            'HTTP_PAYPAL_TRANSMISSION_SIG' => 'test-signature',
            'HTTP_PAYPAL_TRANSMISSION_TIME' => now()->toISOString()
        ]);

        $response->assertStatus(200);

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    /** @test */
    public function paypal_webhook_payment_failed()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-FAIL',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'total' => 200.00,
            'paypal_order_id' => 'PAY-FAIL-TEST'
        ]);

        $webhookPayload = [
            'id' => 'WH-FAIL-EVENT-123',
            'event_type' => 'PAYMENT.CAPTURE.DENIED',
            'resource' => [
                'id' => 'PAY-FAIL-TEST',
                'status' => 'DENIED',
                'status_details' => [
                    'reason' => 'INSUFFICIENT_FUNDS'
                ]
            ]
        ];

        $response = $this->post(route('webhooks.paypal'), $webhookPayload, [
            'HTTP_PAYPAL_TRANSMISSION_ID' => 'test-transmission-id-fail',
            'HTTP_PAYPAL_CERT_ID' => 'test-cert-id',
            'HTTP_PAYPAL_TRANSMISSION_SIG' => 'test-signature',
            'HTTP_PAYPAL_TRANSMISSION_TIME' => now()->toISOString()
        ]);

        $response->assertStatus(200);

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function paypal_refund_processes_successfully()
    {
        Http::fake([
            'api.sandbox.paypal.com/v2/payments/captures/CAPTURE123456789/refund' => Http::response([
                'id' => 'REFUND123456789',
                'status' => 'COMPLETED',
                'amount' => [
                    'currency_code' => 'GBP',
                    'value' => '200.00'
                ]
            ], 201),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-REFUND',
            'payment_method' => 'paypal',
            'status' => 'paid',
            'total' => 200.00,
            'paypal_capture_id' => 'CAPTURE123456789'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Customer request',
            'amount' => 200.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify PayPal refund API was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/refund') &&
                   $request->method() === 'POST';
        });
    }

    /** @test */
    public function paypal_partial_refund_works()
    {
        Http::fake([
            'api.sandbox.paypal.com/v2/payments/captures/CAPTURE123456789/refund' => Http::response([
                'id' => 'PARTIAL-REFUND-123',
                'status' => 'COMPLETED',
                'amount' => [
                    'currency_code' => 'GBP',
                    'value' => '100.00'
                ]
            ], 201),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-PARTIAL',
            'payment_method' => 'paypal',
            'status' => 'paid',
            'total' => 200.00,
            'paypal_capture_id' => 'CAPTURE123456789'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Partial cancellation',
            'amount' => 100.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify partial refund amount
        Http::assertSent(function ($request) {
            $data = json_decode($request->body(), true);
            return isset($data['amount']['value']) && $data['amount']['value'] === '100.00';
        });
    }

    /** @test */
    public function paypal_order_creation_failure_handles_gracefully()
    {
        Http::fake([
            'api.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'error' => 'INVALID_REQUEST',
                'error_description' => 'Request is not well-formed, syntactically incorrect, or violates schema.'
            ], 400),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'paypal'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'paypal'
        ]);
    }

    /** @test */
    public function paypal_webhook_signature_validation()
    {
        // Mock webhook signature validation
        Http::fake([
            'api.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PAYPAL-SIG',
            'payment_method' => 'paypal',
            'status' => 'pending',
            'total' => 200.00,
            'paypal_order_id' => 'PAY-SIG-TEST'
        ]);

        $webhookPayload = [
            'id' => 'WH-SIG-EVENT-123',
            'event_type' => 'CHECKOUT.ORDER.COMPLETED',
            'resource' => [
                'id' => 'PAY-SIG-TEST',
                'status' => 'COMPLETED'
            ]
        ];

        $response = $this->post(route('webhooks.paypal'), $webhookPayload, [
            'HTTP_PAYPAL_TRANSMISSION_ID' => 'valid-transmission-id',
            'HTTP_PAYPAL_CERT_ID' => 'valid-cert-id',
            'HTTP_PAYPAL_TRANSMISSION_SIG' => 'valid-signature',
            'HTTP_PAYPAL_TRANSMISSION_TIME' => now()->toISOString()
        ]);

        $response->assertStatus(200);

        // Verify signature validation was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'verify-webhook-signature');
        });
    }

    /** @test */
    public function paypal_webhook_invalid_signature_rejected()
    {
        Http::fake([
            'api.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'FAILURE'
            ], 200),
        ]);

        $webhookPayload = ['test' => 'invalid webhook'];

        $response = $this->post(route('webhooks.paypal'), $webhookPayload, [
            'HTTP_PAYPAL_TRANSMISSION_ID' => 'invalid-transmission-id',
            'HTTP_PAYPAL_CERT_ID' => 'invalid-cert-id',
            'HTTP_PAYPAL_TRANSMISSION_SIG' => 'invalid-signature',
            'HTTP_PAYPAL_TRANSMISSION_TIME' => now()->toISOString()
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function paypal_payment_logs_transaction_details()
    {
        Log::fake();

        Http::fake([
            'api.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAY-LOG-TEST',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAY-LOG-TEST',
                        'rel' => 'approve',
                        'method' => 'GET'
                    ]
                ]
            ], 201),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $this->post(route('user.process.payment'), [
            'payment_method' => 'paypal'
        ]);

        // Verify PayPal order creation was logged
        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'PayPal order created successfully') &&
                   isset($context['paypal_order_id']) &&
                   isset($context['user_id']) &&
                   isset($context['amount']);
        });
    }
}