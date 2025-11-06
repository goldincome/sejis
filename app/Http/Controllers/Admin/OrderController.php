<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Enums\OrderStatusEnum;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;
    protected $inventoryService;

    public function __construct(OrderService $orderService, InventoryService $inventoryService)
    {
        $this->orderService = $orderService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = $this->orderService->getAllOrders(null);
        return view('admin.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('user', 'orderDetails.product'); // Eager load relations
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $users = User::all();
        $statuses = OrderStatusEnum::cases();
        return view('admin.orders.edit', compact('order', 'users', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, Order $order)
    {
        $this->orderService->updateOrder($order, $request->validated());

        return redirect()->route('admin.orders.index')
                         ->with('success', 'Order updated successfully.');
    }
    
    /**
     * Reserve stock for an order
     */
    public function reserveStock(Order $order): JsonResponse
    {
        try {
            $result = $this->orderService->manualReserveStock($order);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock reserved successfully',
                    'reservations' => $result['reservations']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to reserve stock for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve stock: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Release stock reservations for an order
     */
    public function releaseStock(Order $order): JsonResponse
    {
        try {
            $result = $this->orderService->manualReleaseStock($order);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock reservations released successfully',
                    'released_items' => $result['released_items']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to release stock reservations for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to release stock reservations: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process order dispatch (convert reservations to rental out)
     */
    public function processDispatch(Order $order): JsonResponse
    {
        try {
            $result = $this->orderService->manualProcessDispatch($order, auth()->user());
            
            if ($result['success']) {
                // Update order status to in_transit
                $this->orderService->updateOrder($order, [
                    'status' => 'in_transit'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order dispatch processed successfully',
                    'rented_items' => $result['rented_items']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process order dispatch', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process dispatch: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process order return
     */
    public function processReturn(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'returned_items' => 'required|array',
            'returned_items.*.inventory_item_id' => 'required|integer|exists:inventory_items,id',
            'returned_items.*.quantity' => 'required|integer|min:1',
            'returned_items.*.condition' => 'required|in:excellent,good,fair,poor,needs_repair',
            'returned_items.*.notes' => 'nullable|string|max:500'
        ]);
        
        try {
            $result = $this->orderService->manualProcessReturn(
                $order, 
                $request->input('returned_items'), 
                auth()->user()
            );
            
            if ($result['success']) {
                // Update order status to completed
                $this->orderService->updateOrder($order, [
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order return processed successfully',
                    'returned_items' => $result['returned_items']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process order return', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check inventory availability for order
     */
    public function checkInventoryAvailability(Order $order): JsonResponse
    {
        try {
            $order->load('orderDetails.product');
            
            // Get booking dates from order details
            $bookingDates = $order->orderDetails
                ->pluck('booked_date')
                ->filter()
                ->unique()
                ->sort();
                
            if ($bookingDates->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No booking dates found for this order'
                ], 400);
            }
            
            $startDate = \Carbon\Carbon::parse($bookingDates->first());
            $endDate = \Carbon\Carbon::parse($bookingDates->last())->addDay(); // Add buffer day
            
            $cartItems = $order->orderDetails->map(function ($detail) {
                return [
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity
                ];
            })->toArray();
            
            $availability = $this->orderService->checkOrderAvailability($cartItems, $startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'availability' => $availability,
                'booking_period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check inventory availability for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get inventory status for order
     */
    public function getInventoryStatus(Order $order): JsonResponse
    {
        try {
            $order->load(['orderDetails.product']);
            
            $inventoryStatus = [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'order_status' => $order->status,
                'products' => []
            ];
            
            foreach ($order->orderDetails as $detail) {
                $product = $detail->product;
                
                // Get inventory movements for this order and product
                $movements = \App\Models\InventoryMovement::where('order_id', $order->id)
                    ->whereHas('inventoryItem', function ($query) use ($product) {
                        $query->where('product_id', $product->id);
                    })
                    ->with('inventoryItem')
                    ->get();
                
                $reservations = $movements->where('movement_type', 'reservation');
                $rentalOuts = $movements->where('movement_type', 'rental_out');
                $rentalReturns = $movements->where('movement_type', 'rental_return');
                
                $inventoryStatus['products'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_ordered' => $detail->quantity,
                    'quantity_reserved' => $reservations->sum(function ($m) { return abs($m->quantity_change); }),
                    'quantity_dispatched' => $rentalOuts->sum(function ($m) { return abs($m->quantity_change); }),
                    'quantity_returned' => $rentalReturns->sum('quantity_change'),
                    'movements' => $movements->map(function ($movement) {
                        return [
                            'id' => $movement->id,
                            'type' => $movement->movement_type,
                            'quantity_change' => $movement->quantity_change,
                            'date' => $movement->movement_date,
                            'item_sku' => $movement->inventoryItem->sku,
                            'reason' => $movement->reason,
                            'user' => $movement->user ? $movement->user->name : 'System'
                        ];
                    })
                ];
            }
            
            return response()->json([
                'success' => true,
                'inventory_status' => $inventoryStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get inventory status for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get inventory status: ' . $e->getMessage()
            ], 500);
        }
    }

}
