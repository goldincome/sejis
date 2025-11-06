<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Exceptions\PaymentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'user_type' => 'customer'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Test Kitchen Rental',
            'price' => 100.00,
            'is_active' => true
        ]);
        
        $this->cartService = app(CartService::class);
        
        // Mock HTTP responses for all payment gateways
        $this->mockPaymentGatewayResponses();
    }

    private function mockPaymentGatewayResponses(): void
    {
        Http::fake([
            // Stripe API responses
            'api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_123456789',
                'status' => 'succeeded',
                'amount' => 10000,
                'currency' => 'gbp',
                'client_secret' => 'pi_test_123456789_secret_test'
            ], 200),
            
            'api.stripe.com/v1/payment_intents/pi_test_fail' => Http::response([
                'error' => [
                    'type' => 'card_error',
                    'code' => 'card_declined',
                    'message' => 'Your card was declined.'
                ]
            ], 402),
            
            // PayPal API responses
            'api.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAY-123456789',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAY-123456789',
                        'rel' => 'approve',
                        'method' => 'GET'
                    ]
                ]
            ], 201),
            
            'api.paypal.com/v2/checkout/orders/PAY-123456789/capture' => Http::response([
                'id' => 'PAY-123456789',
                'status' => 'COMPLETED',
                'payment_source' => [
                    'paypal' => [
                        'email_address' => 'test@example.com'
                    ]
                ]
            ], 200),
            
            // TakePayments mock responses
            config('takepayment.direct_url') => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'AUTHCODE:123456',
                'transactionUnique' => 'txn_' . uniqid(),
                'authorisationCode' => '123456'
            ], 200),
            
            // TakePayments failure response
            config('takepayment.direct_url') . '_fail' => Http::response([
                'responseCode' => '5',
                'responseMessage' => 'CARD DECLINED',
                'transactionUnique' => 'txn_' . uniqid()
            ], 200),
        ]);
    }

    /** @test */
    public function stripe_payment_processes_successfully()
    {
        $this->actingAs($this->user);
        
        // Add item to cart
        $this->cartService->addToCart($this->product, [
            'quantity' => 1,
            'booked_date' => now()->addDays(7)->format('Y-m-d'),
            'booking_time' => '1000-1200'
        ]);
        
        // Process payment
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
            'stripe_payment_method' => 'pm_card_visa'
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'stripe',
            'status' => 'paid',
            'total' => 100.00
        ]);
        
        // Verify HTTP request was made to Stripe
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.stripe.com/v1/payment_intents';
        });
    }

    /** @test */
    public function stripe_payment_failure_handles_gracefully()
    {
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'error' => [
                    'type' => 'card_error',
                    'code' => 'card_declined',
                    'message' => 'Your card was declined.'
                ]
            ], 402),
        ]);
        
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_chargeDeclined',
        ]);
        
        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        
        // Ensure no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'stripe'
        ]);
    }

    /** @test */
    public function paypal_payment_creates_order_successfully()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'paypal'
        ]);
        
        $response->assertRedirect();
        
        // Should redirect to PayPal for approval
        $this->assertTrue(str_contains($response->getTargetUrl(), 'paypal.com') || 
                         $response->isRedirection());
        
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.paypal.com');
        });
    }

    /** @test */
    public function paypal_return_processes_payment_completion()
    {
        $this->actingAs($this->user);
        
        // Create a pending order
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-' . uniqid(),
            'payment_method' => 'paypal',
            'status' => 'pending',
            'sub_total' => 100.00,
            'tax' => 20.00,
            'total' => 120.00
        ]);
        
        $response = $this->get(route('user.payment.paypal.return', [
            'token' => 'PAY-123456789',
            'PayerID' => 'PAYER123',
            'order_id' => $order->id
        ]));
        
        $response->assertRedirect(route('user.checkout.success', $order));
        
        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    /** @test */
    public function takepayments_gateway_processes_successfully()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'takepayments',
            'status' => 'paid'
        ]);
    }

    /** @test */
    public function takepayments_declined_card_handles_failure()
    {
        Http::fake([
            config('takepayment.direct_url') => Http::response([
                'responseCode' => '5',
                'responseMessage' => 'CARD DECLINED'
            ], 200),
        ]);
        
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);
        
        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'takepayments',
            'status' => 'paid'
        ]);
    }

    /** @test */
    public function bank_deposit_creates_pending_order()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'bank_deposit'
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Bank deposit should create pending order
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'bank_deposit',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function payment_failure_recovery_works()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        // Simulate payment failure
        Http::fake([
            'api.stripe.com/*' => Http::response([], 500),
        ]);
        
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
        ]);
        
        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        
        // Verify cart is preserved for retry
        $this->assertNotEmpty($this->cartService->getContent());
    }

    /** @test */
    public function webhook_security_validation_works()
    {
        $payload = json_encode(['id' => 'evt_test_webhook']);
        $timestamp = time();
        $secret = config('services.stripe.webhook_secret', 'test_secret');
        
        // Create invalid signature
        $signature = 'invalid_signature';
        
        $response = $this->post(route('webhooks.stripe'), [], [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ])->withHeader('Stripe-Signature', "t={$timestamp},v1={$signature}")
          ->withContent($payload);
        
        $response->assertStatus(400);
    }

    /** @test */
    public function payment_audit_logging_records_transactions()
    {
        Log::fake();
        
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
        ]);
        
        // Verify payment was logged
        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Payment processing started') &&
                   isset($context['user_id']) &&
                   isset($context['amount']) &&
                   isset($context['method']);
        });
    }

    /** @test */
    public function empty_cart_prevents_payment_processing()
    {
        $this->actingAs($this->user);
        
        // Don't add anything to cart
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
        ]);
        
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function payment_amount_validation_prevents_tampering()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);
        
        // Try to tamper with amount
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
            'amount' => 1.00, // Trying to pay £1 instead of £100
        ]);
        
        // Should use calculated amount, not submitted amount
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total' => 100.00 // Original price, not tampered amount
        ]);
    }
}