<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PaymentGateways\BankDepositGateway;
use App\Enums\OrderStatusEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class BankDepositTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Product $product;
    private CartService $cartService;
    private BankDepositGateway $bankDepositGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'bankdeposit@test.com',
            'user_type' => UserTypeEnum::CUSTOMER->value
        ]);
        
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'user_type' => UserTypeEnum::ADMIN->value
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Gourmet Kitchen Rental',
            'price' => 300.00,
            'is_active' => true
        ]);
        
        $this->cartService = app(CartService::class);
        $this->bankDepositGateway = app(BankDepositGateway::class);
    }

    /** @test */
    public function bank_deposit_creates_pending_order_with_instructions()
    {
        Mail::fake();
        
        $this->actingAs($this->user);
        $this->cartService->addToCart($this->product, [
            'quantity' => 1,
            'booked_date' => now()->addDays(6)->format('Y-m-d'),
            'booking_time' => '1000-1400'
        ]);

        $response = $this->post(route('user.process.payment'), [
            'payment_method' => 'bank_deposit'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringContainsString('bank transfer', strtolower(session('success')));

        // Verify pending order was created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('bank_deposit', $order->payment_method);
        $this->assertEquals(OrderStatusEnum::PENDING->value, $order->status);
        $this->assertEquals(300.00, $order->total);
        $this->assertNotNull($order->payment_reference); // Unique reference generated

        // Verify email with bank details was sent
        Mail::assertQueued(function ($mail) use ($order) {
            return $mail->hasTo($this->user->email) &&
                   str_contains($mail->subject, 'Bank Transfer Instructions') &&
                   str_contains($mail->build()->view, $order->order_no);
        });
    }

    /** @test */
    public function bank_deposit_order_shows_payment_instructions()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-001',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'sub_total' => 300.00,
            'tax' => 60.00,
            'total' => 360.00,
            'payment_reference' => 'BANK-REF-' . uniqid(),
            'bank_transfer_details' => json_encode([
                'bank_name' => 'Sejis Business Bank',
                'sort_code' => '12-34-56',
                'account_number' => '12345678',
                'account_name' => 'Sejis Kitchen Rental Ltd',
                'reference' => 'BANK-REF-' . uniqid()
            ])
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('user.checkout.success', $order));

        $response->assertStatus(200);
        $response->assertSee('Bank Transfer Instructions');
        $response->assertSee('12-34-56'); // Sort code
        $response->assertSee('12345678'); // Account number
        $response->assertSee($order->payment_reference); // Payment reference
        $response->assertSee('£360.00'); // Total amount
    }

    /** @test */
    public function admin_can_view_pending_bank_deposit_orders()
    {
        $order1 = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-PENDING-1',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-001'
        ]);

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-PENDING-2',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 450.00,
            'payment_reference' => 'BANK-REF-002'
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.orders.index', ['payment_method' => 'bank_deposit', 'status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($order1->order_no);
        $response->assertSee($order2->order_no);
        $response->assertSee('BANK-REF-001');
        $response->assertSee('BANK-REF-002');
        $response->assertSee('Confirm Payment'); // Action button
    }

    /** @test */
    public function admin_can_confirm_bank_deposit_payment()
    {
        Event::fake();
        
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-CONFIRM',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-CONFIRM'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.orders.confirm-payment', $order), [
            'amount_received' => 300.00,
            'bank_reference' => 'BANK-TXN-123456',
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Payment confirmed via online banking'
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        // Verify order status updated
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::PAID->value, $order->status);
        $this->assertEquals('BANK-TXN-123456', $order->bank_transaction_reference);
        $this->assertNotNull($order->payment_confirmed_at);
        $this->assertEquals($this->admin->id, $order->payment_confirmed_by);

        // Verify payment confirmation event was fired
        Event::assertDispatched(function ($event) use ($order) {
            return $event->order->id === $order->id &&
                   get_class($event) === 'App\Events\PaymentConfirmed';
        });
    }

    /** @test */
    public function admin_can_reject_bank_deposit_payment()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-REJECT',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-REJECT'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.orders.reject-payment', $order), [
            'reason' => 'Incorrect amount received',
            'notes' => 'Customer transferred £250 instead of £300'
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        // Verify order status updated
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::CANCELLED->value, $order->status);
        $this->assertEquals('Incorrect amount received', $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);
        $this->assertEquals($this->admin->id, $order->cancelled_by);
    }

    /** @test */
    public function bank_deposit_partial_payment_handling()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-PARTIAL',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-PARTIAL'
        ]);

        $this->actingAs($this->admin);

        // Confirm partial payment
        $response = $this->post(route('admin.orders.confirm-payment', $order), [
            'amount_received' => 200.00, // Partial payment
            'bank_reference' => 'BANK-PARTIAL-123',
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Partial payment received, waiting for remaining £100'
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('info'); // Info message about partial payment

        // Verify order stays pending with partial payment recorded
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::PENDING->value, $order->status);
        $this->assertEquals(200.00, $order->amount_paid);
        $this->assertEquals(100.00, $order->amount_outstanding);
    }

    /** @test */
    public function bank_deposit_overpayment_handling()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-OVER',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-OVER'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.orders.confirm-payment', $order), [
            'amount_received' => 350.00, // Overpayment
            'bank_reference' => 'BANK-OVER-123',
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Customer overpaid by £50'
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('warning'); // Warning about overpayment

        // Verify order is marked as paid with overpayment recorded
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::PAID->value, $order->status);
        $this->assertEquals(350.00, $order->amount_paid);
        $this->assertEquals(50.00, $order->overpayment_amount);
    }

    /** @test */
    public function bank_deposit_payment_deadline_check()
    {
        // Create order with payment deadline
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-DEADLINE',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-DEADLINE',
            'payment_deadline' => now()->addDays(3) // 3 days to pay
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('user.checkout.success', $order));

        $response->assertStatus(200);
        $response->assertSee('Payment Deadline');
        $response->assertSee(now()->addDays(3)->format('M d, Y'));
    }

    /** @test */
    public function bank_deposit_expired_order_handling()
    {
        // Create expired order
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-EXPIRED',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-EXPIRED',
            'payment_deadline' => now()->subDays(1) // Already expired
        ]);

        // Run scheduled command to check expired orders
        $this->artisan('orders:check-expired')
             ->assertExitCode(0);

        // Verify order was cancelled
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::CANCELLED->value, $order->status);
        $this->assertEquals('Payment deadline expired', $order->cancellation_reason);
    }

    /** @test */
    public function bank_deposit_generates_unique_reference()
    {
        $this->actingAs($this->user);
        
        // Create multiple orders to test uniqueness
        for ($i = 0; $i < 5; $i++) {
            $this->cartService->addToCart($this->product, ['quantity' => 1]);
            
            $this->post(route('user.process.payment'), [
                'payment_method' => 'bank_deposit'
            ]);
            
            $this->cartService->clear(); // Clear cart for next order
        }

        // Verify all orders have unique payment references
        $orders = Order::where('payment_method', 'bank_deposit')->get();
        $references = $orders->pluck('payment_reference')->toArray();
        
        $this->assertEquals(5, count($references));
        $this->assertEquals(5, count(array_unique($references))); // All should be unique
    }

    /** @test */
    public function bank_deposit_admin_bulk_confirm_payments()
    {
        // Create multiple pending orders
        $orders = [];
        for ($i = 0; $i < 3; $i++) {
            $orders[] = Order::create([
                'user_id' => $this->user->id,
                'order_no' => 'ORD-BULK-' . ($i + 1),
                'payment_method' => 'bank_deposit',
                'status' => OrderStatusEnum::PENDING->value,
                'total' => 100.00 * ($i + 1),
                'payment_reference' => 'BANK-BULK-' . ($i + 1)
            ]);
        }

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.orders.bulk-confirm-payments'), [
            'order_ids' => collect($orders)->pluck('id')->toArray(),
            'bank_statement_file' => 'bank_statement.csv',
            'confirmation_date' => now()->format('Y-m-d')
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify all orders were confirmed
        foreach ($orders as $order) {
            $order->refresh();
            $this->assertEquals(OrderStatusEnum::PAID->value, $order->status);
        }
    }

    /** @test */
    public function bank_deposit_logs_all_payment_activities()
    {
        Log::fake();

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_no' => 'ORD-BANK-LOG',
            'payment_method' => 'bank_deposit',
            'status' => OrderStatusEnum::PENDING->value,
            'total' => 300.00,
            'payment_reference' => 'BANK-REF-LOG'
        ]);

        $this->actingAs($this->admin);

        $this->post(route('admin.orders.confirm-payment', $order), [
            'amount_received' => 300.00,
            'bank_reference' => 'BANK-LOG-123',
            'received_date' => now()->format('Y-m-d')
        ]);

        // Verify payment activities were logged
        Log::assertLogged('info', function ($message, $context) use ($order) {
            return str_contains($message, 'Bank deposit payment confirmed') &&
                   isset($context['order_id']) &&
                   $context['order_id'] === $order->id &&
                   isset($context['admin_id']) &&
                   isset($context['amount_received']);
        });
    }
}