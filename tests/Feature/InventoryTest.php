<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Services\InventoryService;
use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Carbon\Carbon;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;
    protected User $testUser;
    protected Category $testCategory;
    protected Product $testProduct;
    protected InventoryItem $testInventoryItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = app(InventoryService::class);

        // Create test data
        $this->testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $this->testCategory = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category for inventory testing'
        ]);

        $this->testProduct = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'category_id' => $this->testCategory->id,
            'description' => 'Test product for inventory testing',
            'price' => 50.00,
            'status' => 'active'
        ]);

        $this->testInventoryItem = InventoryItem::create([
            'product_id' => $this->testProduct->id,
            'sku' => 'TEST-001',
            'quantity_on_hand' => 10,
            'quantity_reserved' => 0,
            'minimum_stock_level' => 2,
            'maximum_stock_level' => 20,
            'location' => 'Main Warehouse',
            'condition' => 'good',
            'purchase_cost' => 40.00,
            'current_value' => 45.00,
            'purchase_date' => now()->subDays(30),
            'is_active' => true,
            'is_rentable' => true
        ]);
    }

    /** @test */
    public function it_can_check_stock_availability_for_single_product()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);

        $availability = $this->inventoryService->checkAvailability(
            $this->testProduct->id, 
            5, 
            $startDate, 
            $endDate
        );

        $this->assertTrue($availability['available']);
        $this->assertEquals(5, $availability['quantity_requested']);
        $this->assertEquals(10, $availability['quantity_available']);
        $this->assertCount(1, $availability['available_items']);
    }

    /** @test */
    public function it_returns_false_when_insufficient_stock_available()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);

        $availability = $this->inventoryService->checkAvailability(
            $this->testProduct->id, 
            15, // More than available
            $startDate, 
            $endDate
        );

        $this->assertFalse($availability['available']);
        $this->assertEquals(15, $availability['quantity_requested']);
        $this->assertEquals(10, $availability['quantity_available']);
    }

    /** @test */
    public function it_can_check_bulk_availability_for_multiple_products()
    {
        // Create another product and inventory item
        $product2 = Product::create([
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'category_id' => $this->testCategory->id,
            'description' => 'Second test product',
            'price' => 75.00,
            'status' => 'active'
        ]);

        InventoryItem::create([
            'product_id' => $product2->id,
            'sku' => 'TEST-002',
            'quantity_on_hand' => 5,
            'quantity_reserved' => 0,
            'minimum_stock_level' => 1,
            'location' => 'Main Warehouse',
            'condition' => 'good',
            'is_active' => true,
            'is_rentable' => true
        ]);

        $items = [
            $this->testProduct->id => 3,
            $product2->id => 2
        ];

        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);

        $availability = $this->inventoryService->checkBulkAvailability($items, $startDate, $endDate);

        $this->assertArrayHasKey($this->testProduct->id, $availability);
        $this->assertArrayHasKey($product2->id, $availability);
        $this->assertEquals(10, $availability[$this->testProduct->id]['total_available']);
        $this->assertEquals(5, $availability[$product2->id]['total_available']);
    }

    /** @test */
    public function it_can_reserve_stock_for_order()
    {
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-001',
            'reference' => 'TEST-REF-001',
            'total' => 150.00,
            'sub_total' => 150.00,
            'tax' => 0,
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 3,
            'price' => 50.00,
            'sub_total' => 150.00,
            'product_type' => 'rental'
        ]);

        $result = $this->inventoryService->reserveStock($order);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('reservations', $result);
        
        // Check that stock was reserved
        $this->testInventoryItem->refresh();
        $this->assertEquals(3, $this->testInventoryItem->quantity_reserved);
        $this->assertEquals(7, $this->testInventoryItem->quantity_available);

        // Check movement was recorded
        $movement = InventoryMovement::where('order_id', $order->id)
            ->where('movement_type', 'reservation')
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(-3, $movement->quantity_change);
    }

    /** @test */
    public function it_fails_to_reserve_insufficient_stock()
    {
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-002',
            'reference' => 'TEST-REF-002',
            'total' => 600.00,
            'sub_total' => 600.00,
            'tax' => 0,
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 15, // More than available
            'price' => 40.00,
            'sub_total' => 600.00,
            'product_type' => 'rental'
        ]);

        $result = $this->inventoryService->reserveStock($order);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        
        // Check that no stock was reserved
        $this->testInventoryItem->refresh();
        $this->assertEquals(0, $this->testInventoryItem->quantity_reserved);
        $this->assertEquals(10, $this->testInventoryItem->quantity_available);
    }

    /** @test */
    public function it_can_release_stock_reservations()
    {
        // First, create a reservation
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-003',
            'reference' => 'TEST-REF-003',
            'total' => 200.00,
            'sub_total' => 200.00,
            'tax' => 0,
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 4,
            'price' => 50.00,
            'sub_total' => 200.00,
            'product_type' => 'rental'
        ]);

        // Reserve the stock
        $reserveResult = $this->inventoryService->reserveStock($order);
        $this->assertTrue($reserveResult['success']);

        // Check reservation was made
        $this->testInventoryItem->refresh();
        $this->assertEquals(4, $this->testInventoryItem->quantity_reserved);

        // Now release the reservation
        $releaseResult = $this->inventoryService->releaseReservations($order);

        $this->assertTrue($releaseResult['success']);
        
        // Check that stock was released
        $this->testInventoryItem->refresh();
        $this->assertEquals(0, $this->testInventoryItem->quantity_reserved);
        $this->assertEquals(10, $this->testInventoryItem->quantity_available);
    }

    /** @test */
    public function it_can_process_rental_out()
    {
        // Create and reserve stock first
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-004',
            'reference' => 'TEST-REF-004',
            'total' => 100.00,
            'sub_total' => 100.00,
            'tax' => 0,
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'status' => OrderStatusEnum::CONFIRMED
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 2,
            'price' => 50.00,
            'sub_total' => 100.00,
            'product_type' => 'rental'
        ]);

        // Reserve stock
        $this->inventoryService->reserveStock($order);

        // Process rental out
        $result = $this->inventoryService->processRentalOut($order, $this->testUser);

        $this->assertTrue($result['success']);
        
        // Check stock levels
        $this->testInventoryItem->refresh();
        $this->assertEquals(8, $this->testInventoryItem->quantity_on_hand); // 10 - 2
        $this->assertEquals(0, $this->testInventoryItem->quantity_reserved); // Released during rental out
        $this->assertEquals(8, $this->testInventoryItem->quantity_available);

        // Check rental out movement was recorded
        $movement = InventoryMovement::where('order_id', $order->id)
            ->where('movement_type', 'rental_out')
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(-2, $movement->quantity_change);
    }

    /** @test */
    public function it_can_process_rental_return()
    {
        // Create order and process rental out first
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-005',
            'reference' => 'TEST-REF-005',
            'total' => 150.00,
            'sub_total' => 150.00,
            'tax' => 0,
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'status' => OrderStatusEnum::ACTIVE
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 3,
            'price' => 50.00,
            'sub_total' => 150.00,
            'product_type' => 'rental'
        ]);

        // Reserve and process rental out
        $this->inventoryService->reserveStock($order);
        $this->inventoryService->processRentalOut($order, $this->testUser);

        // Process rental return
        $returnedItems = [
            [
                'inventory_item_id' => $this->testInventoryItem->id,
                'quantity' => 3,
                'condition' => 'good',
                'notes' => 'Items returned in good condition'
            ]
        ];

        $result = $this->inventoryService->processRentalReturn($order, $returnedItems, $this->testUser);

        $this->assertTrue($result['success']);
        
        // Check stock levels
        $this->testInventoryItem->refresh();
        $this->assertEquals(10, $this->testInventoryItem->quantity_on_hand); // Back to original
        $this->assertEquals(10, $this->testInventoryItem->quantity_available);

        // Check rental return movement was recorded
        $movement = InventoryMovement::where('order_id', $order->id)
            ->where('movement_type', 'rental_return')
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(3, $movement->quantity_change);
    }

    /** @test */
    public function it_can_add_new_stock()
    {
        $itemDetails = [
            'location' => 'Main Warehouse',
            'condition' => 'excellent',
            'purchase_cost' => 45.00,
            'supplier' => 'Test Supplier',
            'reason' => 'New stock purchase'
        ];

        $result = $this->inventoryService->addStock(
            $this->testProduct->id, 
            5, 
            $itemDetails, 
            $this->testUser
        );

        $this->assertTrue($result['success']);
        
        // Check stock was added to existing item
        $this->testInventoryItem->refresh();
        $this->assertEquals(15, $this->testInventoryItem->quantity_on_hand); // 10 + 5

        // Check movement was recorded
        $movement = InventoryMovement::where('movement_type', 'stock_in')
            ->where('inventory_item_id', $this->testInventoryItem->id)
            ->latest()
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(5, $movement->quantity_change);
    }

    /** @test */
    public function it_detects_low_stock_items()
    {
        // Set stock to low level
        $this->testInventoryItem->update(['quantity_on_hand' => 1]); // Below minimum of 2

        $alerts = $this->inventoryService->getLowStockAlerts();

        $this->assertCount(1, $alerts);
        $this->assertEquals($this->testInventoryItem->id, $alerts[0]['inventory_item_id']);
        $this->assertEquals('critical', $alerts[0]['alert_level']);
    }

    /** @test */
    public function it_calculates_dashboard_stats_correctly()
    {
        // Create additional inventory items for testing stats
        $product2 = Product::create([
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'category_id' => $this->testCategory->id,
            'price' => 100.00,
            'status' => 'active'
        ]);

        InventoryItem::create([
            'product_id' => $product2->id,
            'sku' => 'TEST-002',
            'quantity_on_hand' => 0, // Out of stock
            'minimum_stock_level' => 2,
            'location' => 'Main Warehouse',
            'condition' => 'good',
            'current_value' => 100.00,
            'is_active' => true
        ]);

        $stats = $this->inventoryService->getDashboardStats();

        $this->assertArrayHasKey('inventory_summary', $stats);
        $this->assertArrayHasKey('daily_activity', $stats);
        $this->assertArrayHasKey('alerts', $stats);

        $this->assertEquals(2, $stats['inventory_summary']['total_items']);
        $this->assertEquals(1, $stats['inventory_summary']['out_of_stock_count']);
    }

    /** @test */
    public function it_handles_concurrent_reservations_properly()
    {
        // Test concurrent reservations don't over-reserve stock
        $order1 = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-006',
            'reference' => 'TEST-REF-006',
            'total' => 350.00,
            'sub_total' => 350.00,
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        $order2 = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-007',
            'reference' => 'TEST-REF-007',
            'total' => 300.00,
            'sub_total' => 300.00,
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 7,
            'price' => 50.00,
            'sub_total' => 350.00
        ]);

        OrderDetail::create([
            'order_id' => $order2->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 6,
            'price' => 50.00,
            'sub_total' => 300.00
        ]);

        // Try to reserve for both orders (total 13 items, but only 10 available)
        $result1 = $this->inventoryService->reserveStock($order1);
        $result2 = $this->inventoryService->reserveStock($order2);

        // Only one should succeed
        $this->assertTrue($result1['success']);
        $this->assertFalse($result2['success']);

        // Check stock levels
        $this->testInventoryItem->refresh();
        $this->assertEquals(7, $this->testInventoryItem->quantity_reserved);
        $this->assertEquals(3, $this->testInventoryItem->quantity_available);
    }

    /** @test */
    public function it_maintains_audit_trail_for_all_movements()
    {
        $order = Order::create([
            'user_id' => $this->testUser->id,
            'order_no' => 'ORD-008',
            'reference' => 'TEST-REF-008',
            'total' => 100.00,
            'sub_total' => 100.00,
            'currency' => 'GBP',
            'status' => OrderStatusEnum::PENDING
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'name' => $this->testProduct->name,
            'quantity' => 2,
            'price' => 50.00,
            'sub_total' => 100.00
        ]);

        // Perform various operations
        $this->inventoryService->reserveStock($order);
        $this->inventoryService->processRentalOut($order, $this->testUser);
        
        $returnItems = [[
            'inventory_item_id' => $this->testInventoryItem->id,
            'quantity' => 2,
            'condition' => 'good'
        ]];
        $this->inventoryService->processRentalReturn($order, $returnItems, $this->testUser);

        // Check all movements were recorded
        $movements = InventoryMovement::where('inventory_item_id', $this->testInventoryItem->id)
            ->orderBy('created_at')
            ->get();

        $this->assertGreaterThanOrEqual(3, $movements->count());
        
        // Verify movement types
        $movementTypes = $movements->pluck('movement_type')->toArray();
        $this->assertContains('reservation', $movementTypes);
        $this->assertContains('rental_out', $movementTypes);
        $this->assertContains('rental_return', $movementTypes);
    }

    /** @test */
    public function it_validates_stock_levels_after_operations()
    {
        // Test that stock levels never go negative
        $this->testInventoryItem->update(['quantity_on_hand' => 1]);

        $result = $this->testInventoryItem->adjustStock(-5, 'Test adjustment');
        $this->assertFalse($result);

        // Verify stock wasn't changed
        $this->testInventoryItem->refresh();
        $this->assertEquals(1, $this->testInventoryItem->quantity_on_hand);
    }

    /** @test */
    public function it_handles_maintenance_scheduling_correctly()
    {
        $this->testInventoryItem->update([
            'next_maintenance_due' => Carbon::now()->addDays(5)
        ]);

        $this->assertEquals('due_soon', $this->testInventoryItem->maintenance_status);

        $this->testInventoryItem->update([
            'next_maintenance_due' => Carbon::now()->subDays(1)
        ]);

        $this->testInventoryItem->refresh();
        $this->assertEquals('overdue', $this->testInventoryItem->maintenance_status);
    }

    /** @test */
    public function it_tracks_warranty_status_correctly()
    {
        $this->testInventoryItem->update([
            'warranty_expires' => Carbon::now()->addDays(15)
        ]);

        $this->assertEquals('expiring_soon', $this->testInventoryItem->warranty_status);

        $this->testInventoryItem->update([
            'warranty_expires' => Carbon::now()->subDays(1)
        ]);

        $this->testInventoryItem->refresh();
        $this->assertEquals('expired', $this->testInventoryItem->warranty_status);
    }
}