<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PaymentGateways\StripeGateway;
use App\Exceptions\PaymentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private CartService $cartService;
    private StripeGateway $stripeGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'stripe@test.com',
            'user_type' => 'customer'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Premium Kitchen Rental',
            'price' => 150.00,
            'is_active' => true
        ]);
        
        $this->cartService = app(CartService::class);
        $this->stripeGateway = app(StripeGateway::class);
        
        // Set test Stripe keys
        Config::set('services.stripe.key', 'pk_test_123');
        Config::set('services.stripe.secret', 'sk_test_123');
        Config::set('services.stripe.webhook_secret', 'whsec_test_123');
    }

    /** @test */
    public function successful_stripe_payment_with_payment_intent()
    {
        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_successful',
                'status' => 'succeeded',
                'amount' => 15000, // £150.00 in pence
                'currency' => 'gbp',
                'client_secret' => 'pi_test_successful_secret',
                'charges' => [
                    'data' => [
                        [
                            'id' => 'ch_test_charge',
                            'status' => 'succeeded',
                            'receipt_url' => 'https://pay.stripe.com/receipts/test'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, [
            'quantity' => 1,
            'booked_date' => now()->addDays(5)->format('Y-m-d'),
            'booking_time' => '1400-1600'
        ]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_payment_method' => 'pm_card_visa',
            'stripe_payment_intent' => 'pi_test_successful'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify order creation
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('stripe', $order->payment_method);
        $this->assertEquals('paid', $order->status);
        $this->assertEquals(150.00, $order->total);

        // Verify Stripe API was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.stripe.com/v1/payment_intents');
        });
    }

    /** @test */
    public function stripe_payment_with_insufficient_funds()
    {
        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::response([
                'error' => [
                    'type' => 'card_error',
                    'code' => 'insufficient_funds',
                    'message' => 'Your card has insufficient funds.',
                    'decline_code' => 'insufficient_funds'
                ]
            ], 402),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_payment_method' => 'pm_card_insufficient_funds'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('insufficient funds', session('error'));

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'stripe'
        ]);
    }

    /** @test */
    public function stripe_payment_with_expired_card()
    {
        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::response([
                'error' => [
                    'type' => 'card_error',
                    'code' => 'expired_card',
                    'message' => 'Your card has expired.',
                    'decline_code' => 'expired_card'
                ]
            ], 402),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_payment_method' => 'pm_card_expired'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('expired', session('error'));
    }

    /** @test */
    public function stripe_3d_secure_authentication_required()
    {
        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_3ds',
                'status' => 'requires_action',
                'amount' => 15000,
                'currency' => 'gbp',
                'client_secret' => 'pi_test_3ds_secret',
                'next_action' => [
                    'type' => 'use_stripe_sdk',
                    'use_stripe_sdk' => [
                        'type' => 'three_d_secure_redirect'
                    ]
                ]
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_payment_method' => 'pm_card_threeDSecure2Required'
        ]);

        // Should return JSON for frontend to handle 3D Secure
        $response->assertJson([
            'requires_action' => true,
            'payment_intent_client_secret' => 'pi_test_3ds_secret'
        ]);
    }

    /** @test */
    public function stripe_webhook_payment_succeeded_updates_order()
    {
        // Create pending order
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-STRIPE-001',
            'payment_method' => 'stripe',
            'status' => 'pending',
            'sub_total' => 150.00,
            'tax' => 30.00,
            'total' => 180.00,
            'stripe_payment_intent' => 'pi_webhook_test'
        ]);

        $webhookPayload = [
            'id' => 'evt_test_webhook',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_webhook_test',
                    'status' => 'succeeded',
                    'amount' => 18000,
                    'currency' => 'gbp'
                ]
            ]
        ];

        $timestamp = time();
        $signature = $this->generateStripeSignature(json_encode($webhookPayload), $timestamp);

        $response = $this->post(route('webhooks.stripe'), $webhookPayload, [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}"
        ]);

        $response->assertStatus(200);

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    /** @test */
    public function stripe_webhook_payment_failed_updates_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-STRIPE-002',
            'payment_method' => 'stripe',
            'status' => 'pending',
            'total' => 180.00,
            'stripe_payment_intent' => 'pi_webhook_fail'
        ]);

        $webhookPayload = [
            'id' => 'evt_test_webhook_fail',
            'object' => 'event',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_webhook_fail',
                    'status' => 'payment_failed',
                    'last_payment_error' => [
                        'message' => 'Your card was declined.'
                    ]
                ]
            ]
        ];

        $timestamp = time();
        $signature = $this->generateStripeSignature(json_encode($webhookPayload), $timestamp);

        $response = $this->post(route('webhooks.stripe'), $webhookPayload, [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}"
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function stripe_webhook_invalid_signature_rejected()
    {
        $webhookPayload = ['test' => 'data'];
        $timestamp = time();
        $invalidSignature = 'invalid_signature';

        $response = $this->post(route('webhooks.stripe'), $webhookPayload, [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$invalidSignature}"
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function stripe_refund_processes_successfully()
    {
        Http::fake([
            'api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_test_refund',
                'status' => 'succeeded',
                'amount' => 15000,
                'charge' => 'ch_test_charge'
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-REFUND-001',
            'payment_method' => 'stripe',
            'status' => 'paid',
            'total' => 150.00,
            'stripe_charge_id' => 'ch_test_charge'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Customer request',
            'amount' => 150.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.stripe.com/v1/refunds');
        });
    }

    /** @test */
    public function stripe_partial_refund_works()
    {
        Http::fake([
            'api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_test_partial',
                'status' => 'succeeded',
                'amount' => 7500, // £75.00 partial refund
                'charge' => 'ch_test_charge'
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-PARTIAL-001',
            'payment_method' => 'stripe',
            'status' => 'paid',
            'total' => 150.00,
            'stripe_charge_id' => 'ch_test_charge'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Partial cancellation',
            'amount' => 75.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify partial refund amount
        Http::assertSent(function ($request) {
            $data = json_decode($request->body(), true);
            return $data['amount'] === 7500; // £75.00 in pence
        });
    }

    /** @test */
    public function stripe_payment_logs_transaction_details()
    {
        Log::fake();

        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_logging',
                'status' => 'succeeded',
                'amount' => 15000,
                'currency' => 'gbp'
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_payment_method' => 'pm_card_visa'
        ]);

        // Verify payment logging
        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Stripe payment processed successfully') &&
                   isset($context['payment_intent_id']) &&
                   isset($context['user_id']) &&
                   isset($context['amount']);
        });
    }

    /**
     * Generate valid Stripe webhook signature for testing
     */
    private function generateStripeSignature(string $payload, int $timestamp): string
    {
        $secret = config('services.stripe.webhook_secret');
        return hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }
}