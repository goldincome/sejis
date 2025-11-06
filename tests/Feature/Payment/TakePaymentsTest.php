<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PaymentGateways\TakePaymentGateway;
use App\Exceptions\PaymentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TakePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private CartService $cartService;
    private TakePaymentGateway $takePaymentGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'takepayments@test.com',
            'user_type' => 'customer'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Professional Kitchen Space',
            'price' => 250.00,
            'is_active' => true
        ]);
        
        $this->cartService = app(CartService::class);
        $this->takePaymentGateway = app(TakePaymentGateway::class);
        
        // Set test TakePayments configuration
        Config::set('takepayment.is_live', false);
        Config::set('takepayment.test_access_key', '9GXwHNVC87VqsqNM');
        Config::set('takepayment.test_merchant_id', '119837');
        Config::set('takepayment.hosted_url', 'https://gw1.tponlinepayments.com/hosted/');
        Config::set('takepayment.direct_url', 'https://gw1.tponlinepayments.com/direct/');
    }

    /** @test */
    public function takepayments_hosted_form_submission()
    {
        Http::fake([
            'gw1.tponlinepayments.com/hosted/*' => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'AUTHCODE:123456',
                'transactionUnique' => 'txn_test_123',
                'authorisationCode' => '123456',
                'xref' => 'XREF123456789'
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, [
            'quantity' => 1,
            'booked_date' => now()->addDays(4)->format('Y-m-d'),
            'booking_time' => '1300-1600'
        ]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments'
        ]);

        // TakePayments should return hosted form HTML
        $response->assertStatus(200);
        $this->assertStringContainsString('form', $response->getContent());
        $this->assertStringContainsString('gw1.tponlinepayments.com', $response->getContent());

        // Verify pending order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'takepayments',
            'status' => 'pending',
            'total' => 250.00
        ]);
    }

    /** @test */
    public function takepayments_direct_payment_success()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'AUTHCODE:789012',
                'transactionUnique' => 'txn_direct_456',
                'authorisationCode' => '789012',
                'xref' => 'XREF987654321',
                'amountReceived' => '25000' // £250.00 in pence
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123',
            'cardholderName' => 'John Doe'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify order was created and marked as paid
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'takepayments',
            'status' => 'paid',
            'total' => 250.00
        ]);

        // Verify HTTP request was made to TakePayments
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gw1.tponlinepayments.com/direct/');
        });
    }

    /** @test */
    public function takepayments_direct_payment_declined()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '5',
                'responseMessage' => 'CARD DECLINED',
                'transactionUnique' => 'txn_declined_789'
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('declined', strtolower(session('error')));

        // Verify no paid order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'takepayments',
            'status' => 'paid'
        ]);
    }

    /** @test */
    public function takepayments_return_callback_success()
    {
        // Create pending order
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-TAKE-001',
            'payment_method' => 'takepayments',
            'status' => 'pending',
            'sub_total' => 250.00,
            'tax' => 50.00,
            'total' => 300.00,
            'takepayments_transaction_unique' => 'txn_callback_123'
        ]);

        $response = $this->post(route('takepayment.success'), [
            'responseCode' => '0',
            'responseMessage' => 'AUTHCODE:456789',
            'transactionUnique' => 'txn_callback_123',
            'authorisationCode' => '456789',
            'xref' => 'XREF456789123',
            'orderRef' => $order->order_no,
            'amountReceived' => '30000'
        ]);

        $response->assertRedirect(route('user.checkout.success', $order));

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->takepayments_xref);
    }

    /** @test */
    public function takepayments_return_callback_failed()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-TAKE-FAIL',
            'payment_method' => 'takepayments',
            'status' => 'pending',
            'total' => 300.00,
            'takepayments_transaction_unique' => 'txn_fail_456'
        ]);

        $response = $this->post(route('takepayment.success'), [
            'responseCode' => '5',
            'responseMessage' => 'CARD DECLINED',
            'transactionUnique' => 'txn_fail_456',
            'orderRef' => $order->order_no
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');

        // Verify order status updated to cancelled
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function takepayments_refund_processes_successfully()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'REFUND APPROVED',
                'transactionUnique' => 'txn_refund_789',
                'xref' => 'REFUND123456789'
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-TAKE-REFUND',
            'payment_method' => 'takepayments',
            'status' => 'paid',
            'total' => 250.00,
            'takepayments_xref' => 'XREF123456789'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Customer request',
            'amount' => 250.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify TakePayments refund API was called
        Http::assertSent(function ($request) {
            $data = $request->data();
            return str_contains($request->url(), 'gw1.tponlinepayments.com/direct/') &&
                   isset($data['action']) && $data['action'] === 'REFUND';
        });
    }

    /** @test */
    public function takepayments_partial_refund_works()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'PARTIAL REFUND APPROVED',
                'transactionUnique' => 'txn_partial_refund',
                'xref' => 'PARTIAL_REFUND123',
                'amountRefunded' => '12500' // £125.00 partial refund
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-TAKE-PARTIAL',
            'payment_method' => 'takepayments',
            'status' => 'paid',
            'total' => 250.00,
            'takepayments_xref' => 'XREF987654321'
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.orders.refund', $order), [
            'reason' => 'Partial cancellation',
            'amount' => 125.00
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify partial refund amount
        Http::assertSent(function ($request) {
            $data = $request->data();
            return isset($data['amount']) && $data['amount'] === '12500'; // £125.00 in pence
        });
    }

    /** @test */
    public function takepayments_3d_secure_authentication()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '65802',
                'responseMessage' => '3D Secure authentication required',
                'threeDSRef' => '3DS_REF_123',
                'threeDSURL' => 'https://acs.test.com/3ds/auth',
                'threeDSRequest' => [
                    'PaReq' => 'test_pareq_data',
                    'MD' => 'test_md_data',
                    'TermUrl' => route('takepayment.3ds.return')
                ]
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);

        // Should return 3DS authentication form
        $response->assertStatus(200);
        $this->assertStringContainsString('3D Secure', $response->getContent());
        $this->assertStringContainsString('acs.test.com', $response->getContent());
    }

    /** @test */
    public function takepayments_webhook_signature_validation()
    {
        // Mock webhook data
        $webhookData = [
            'responseCode' => '0',
            'responseMessage' => 'AUTHCODE:999888',
            'transactionUnique' => 'webhook_txn_123',
            'orderRef' => 'ORD-WEBHOOK-001',
            'signature' => hash('sha512', 'webhook_txn_123' . config('takepayment.test_access_key'))
        ];

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-WEBHOOK-001',
            'payment_method' => 'takepayments',
            'status' => 'pending',
            'total' => 250.00
        ]);

        $response = $this->post(route('webhooks.takepayments'), $webhookData);

        $response->assertStatus(200);

        // Verify order status updated
        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    /** @test */
    public function takepayments_webhook_invalid_signature_rejected()
    {
        $webhookData = [
            'responseCode' => '0',
            'responseMessage' => 'AUTHCODE:999888',
            'transactionUnique' => 'invalid_webhook_123',
            'orderRef' => 'ORD-WEBHOOK-INVALID',
            'signature' => 'invalid_signature_hash'
        ];

        $response = $this->post(route('webhooks.takepayments'), $webhookData);

        $response->assertStatus(400);
    }

    /** @test */
    public function takepayments_payment_logs_transaction_details()
    {
        Log::fake();

        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response([
                'responseCode' => '0',
                'responseMessage' => 'AUTHCODE:555444',
                'transactionUnique' => 'txn_log_test',
                'authorisationCode' => '555444'
            ], 200),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);

        // Verify TakePayments transaction was logged
        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'TakePayments payment processed successfully') &&
                   isset($context['transaction_unique']) &&
                   isset($context['user_id']) &&
                   isset($context['amount']);
        });
    }

    /** @test */
    public function takepayments_handles_network_timeout()
    {
        Http::fake([
            'gw1.tponlinepayments.com/direct/' => Http::response(null, 500),
        ]);

        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '12',
            'cardExpiryYear' => '2025',
            'cardCVV' => '123'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('network', strtolower(session('error')));
    }

    /** @test */
    public function takepayments_validates_card_expiry_date()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        // Test with expired card
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            'cardNumber' => '4000000000000002',
            'cardExpiryMonth' => '01',
            'cardExpiryYear' => '2020', // Expired year
            'cardCVV' => '123'
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHasErrors(['cardExpiryYear']);
    }

    /** @test */
    public function takepayments_validates_required_card_fields()
    {
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, ['quantity' => 1]);

        // Test with missing required fields
        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'takepayments',
            'payment_type' => 'direct',
            // Missing cardNumber, cardExpiryMonth, cardExpiryYear, cardCVV
        ]);

        $response->assertRedirect(route('user.checkout.index'));
        $response->assertSessionHasErrors([
            'cardNumber', 'cardExpiryMonth', 'cardExpiryYear', 'cardCVV'
        ]);
    }
}