<?php
namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Enums\PaymentStatusEnum;
use Gloudemans\Shoppingcart\Facades\Cart;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Services\InventoryService;

class OrderService
{
    protected NotificationService $notificationService;
    protected InventoryService $inventoryService;

    public function __construct(NotificationService $notificationService, InventoryService $inventoryService)
    {
        $this->notificationService = $notificationService;
        $this->inventoryService = $inventoryService;
    }

   public function createOrder(array $orderData): Order
    {   
        foreach(Cart::content() as $cartItem) { 
            $orderData['payment_method'] = $cartItem->options->payment_method;
        }
        
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_no' => $this->generateOrderNumber(),
            //'reference' => $this->generateOrderReference(),
            'payment_method' => $orderData['payment_method'],
            'total' => Cart::total(2, '.', ''),
            'sub_total' => Cart::subtotal(2, '.', ''),
            'tax' => Cart::tax(2, '.', ''),
            'currency' => config('cashier.currency'),
            'status' => OrderStatusEnum::PAID->value,
        ]);
        
        //create order details
        $this->createOrderDetails($order);
        
        // Send email notifications
        try {
            // Load order with necessary relationships for email
            $order->load(['user', 'orderDetails.product']);
            
            // Send order event notifications (customer confirmation + admin alerts)
            $this->notificationService->sendOrderEventNotifications($order, 'order_placed', [
                'admin_note' => "New order placed by {$order->user->name}"
            ]);
            
            Log::info('Order created and notifications sent', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'customer_id' => $order->user_id
            ]);
            
        } catch (\Exception $e) {
            // Don't fail order creation if email fails
            Log::error('Failed to send order creation notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $order;
    }


    protected function createOrderDetails(Order $order)
    {   
        foreach(Cart::content() as $index =>  $cartItem) {
            
            $orderDetail = OrderDetail::create([
                'name' => $cartItem->name,
                'ref_no' => $this->generateOrderNumber().$index,
                'order_id' => $order->id,
                'product_id' => $cartItem->options->product_model_id,
                'quantity' => $cartItem->qty,
                'price' => $cartItem->price,
                'sub_total' => $cartItem->subtotal,
                'booked_date' => $cartItem->options->booking_date ?? null,
                'start_date' => $cartItem->options->start_date ?? null,
                'end_date' => $cartItem->options->end_date ?? null,
                'product_type' => $cartItem->options->product_type,
                'booked_durations' => $cartItem->options->booking_time_raw ? getAllBookingTimeJson($cartItem->options->booking_date, $cartItem->options->booking_time_raw) : null,
            ]);
        }
    }

    protected function generateOrderNumber()
    {
        $number = mt_rand(1000000000, 9999999999); // better than rand()

        // call the same function if the number exists already
        if ($this->orderNumberExists($number)) {
            return $this->generateOrderNumber();
        }
        // otherwise, it's valid and can be used
        return $number;
    }

    protected function generateOrderReference(): string
    {
        do {
            $reference = 'ORD-' . strtoupper(uniqid());
        } while (Order::where('reference', $reference)->exists());
        
        return $reference;
    }

    protected function orderNumberExists($number)
    {
        return Order::where('order_no', $number)->exists();
    }

    public function getAllOrders(?User $user)
    {
        if($user){
            return $user->orders()->latest()->paginate();
        }
        return Order::with('user')->latest()->paginate(10);
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $oldStatus = $order->status;
        $order->update($data);
        
        // Handle inventory operations based on status changes
        if (isset($data['status']) && $oldStatus !== $data['status']) {
            $this->handleInventoryStatusChange($order, $oldStatus, $data['status']);
            
            try {
                $order->load(['user', 'orderDetails.product']);
                
                // Send status update to customer
                $this->notificationService->sendOrderStatusUpdate(
                    $order, 
                    $oldStatus, 
                    $data['status'],
                    $data['status_update_note'] ?? null
                );
                
                // Send admin notification for certain status changes
                $adminNotificationStatuses = ['cancelled', 'refund_requested', 'payment_failed'];
                if (in_array($data['status'], $adminNotificationStatuses)) {
                    $notificationType = match($data['status']) {
                        'cancelled' => 'cancellation_request',
                        'refund_requested' => 'refund_request',
                        'payment_failed' => 'payment_failed',
                        default => 'order_status_change'
                    };
                    
                    $this->notificationService->sendAdminOrderNotification(
                        $order,
                        $notificationType,
                        "Status changed from {$oldStatus} to {$data['status']}"
                    );
                }
                
                Log::info('Order updated and notifications sent', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $data['status']
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to send order update notifications', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $data['status'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $order;
    }

    /**
     * Get user orders with optional limit
     */
    public function getUserOrders(int $userId, int $limit = null)
    {
        $query = Order::where('user_id', $userId)
            ->with(['orderDetails.product'])
            ->latest();
        
        if ($limit) {
            return $query->limit($limit)->get();
        }
        
        return $query->paginate(15);
    }

    /**
     * Get user orders with filtering and pagination
     */
    public function getUserOrdersWithFilters(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::where('user_id', $userId)
            ->with(['orderDetails.product', 'bankDeposit']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->Where('order_no', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('orderDetails', function ($orderDetailQuery) use ($filters) {
                      $orderDetailQuery->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get user statistics for dashboard
     */
    public function getUserStats(int $userId): array
    {
        $totalOrders = Order::where('user_id', $userId)->count();
        $completedOrders = Order::where('user_id', $userId)->where('status', 'completed')->count();
        $pendingOrders = Order::where('user_id', $userId)->where('status', 'pending')->count();
        $totalSpent = Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed', 'delivered'])
            ->sum('total');
        
        // Get monthly spending for the current year
        $monthlySpending = Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed', 'delivered'])
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
        
        // Get upcoming kitchen rentals
        $upcomingRentals = Order::where('user_id', $userId)
            ->whereHas('orderDetails', function ($query) {
                $query->where('booked_date', '>=', now()->toDateString());
            })
            ->whereIn('status', ['paid', 'confirmed'])
            ->count();
        
        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'total_spent' => $totalSpent,
            'monthly_spending' => $monthlySpending,
            'upcoming_rentals' => $upcomingRentals,
            'completion_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0
        ];
    }

    /**
     * Get upcoming orders for a user
     */
    public function getUpcomingOrders(int $userId, int $limit = 5)
    {
        return Order::where('user_id', $userId)
            ->whereHas('orderDetails', function ($query) {
                $query->where('booked_date', '>=', now()->toDateString());
            })
            ->whereIn('status', ['paid', 'confirmed'])
            ->with(['orderDetails' => function ($query) {
                $query->where('booked_date', '>=', now()->toDateString())
                      ->orderBy('booked_date');
            }])
            ->limit($limit)
            ->get();
    }

    /**
     * Get order timeline/status history
     */
    public function getOrderTimeline(int $orderId): array
    {
        $order = Order::findOrFail($orderId);
        
        $timeline = [
            [
                'status' => 'pending',
                'title' => 'Order Placed',
                'description' => 'Your order has been placed and is pending payment.',
                'date' => $order->created_at,
                'completed' => true
            ]
        ];
        
        if ($order->paid_at) {
            $timeline[] = [
                'status' => 'paid',
                'title' => 'Payment Confirmed',
                'description' => 'Payment has been successfully processed.',
                'date' => $order->paid_at,
                'completed' => true
            ];
        }
        
        if (in_array($order->status, ['confirmed', 'completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'confirmed',
                'title' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared.',
                'date' => $order->updated_at,
                'completed' => true
            ];
        }
        
        if (in_array($order->status, ['completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'completed',
                'title' => 'Order Completed',
                'description' => 'Your kitchen rental has been completed.',
                'date' => $order->updated_at,
                'completed' => true
            ];
        }
        
        return $timeline;
    }
    
    /**
     * Handle inventory operations based on order status changes
     */
    protected function handleInventoryStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        try {
            $order->load('orderDetails.product');
            
            // Handle status transitions that affect inventory
            match ([$oldStatus, $newStatus]) {
                // When order is confirmed/paid - reserve stock
                ['pending', 'confirmed'],
                ['pending', 'paid'],
                ['confirmed', 'paid'] => $this->reserveOrderStock($order),
                
                // When order is cancelled - release reservations
                ['pending', 'cancelled'],
                ['confirmed', 'cancelled'],
                ['paid', 'cancelled'] => $this->releaseOrderStock($order),
                
                // When items are dispatched/delivered - convert reservations to rental out
                ['confirmed', 'in_transit'],
                ['paid', 'in_transit'],
                ['confirmed', 'delivered'],
                ['paid', 'delivered'] => $this->processOrderDispatch($order),
                
                // When rental is completed - handle return
                ['delivered', 'completed'] => $this->processOrderCompletion($order),
                
                default => null // No inventory action needed
            };
            
        } catch (\Exception $e) {
            Log::error('Inventory status change handling failed', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            
            // Don't fail the order update if inventory operations fail
            // but log the error for investigation
        }
    }
    
    /**
     * Reserve stock for an order
     */
    protected function reserveOrderStock(Order $order): void
    {
        $result = $this->inventoryService->reserveStock($order);
        
        if (!$result['success']) {
            Log::warning('Stock reservation failed for order', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'error' => $result['message'],
                'errors' => $result['errors'] ?? []
            ]);
            
            // Optionally send admin notification about stock issues
            try {
                $this->notificationService->sendAdminOrderNotification(
                    $order,
                    'stock_shortage',
                    'Failed to reserve stock: ' . $result['message']
                );
            } catch (\Exception $e) {
                Log::error('Failed to send stock shortage notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::info('Stock reserved successfully for order', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'reservations' => $result['reservations']
            ]);
        }
    }
    
    /**
     * Release stock reservations for an order
     */
    protected function releaseOrderStock(Order $order): void
    {
        $result = $this->inventoryService->releaseReservations($order);
        
        if ($result['success']) {
            Log::info('Stock reservations released for cancelled order', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'released_items' => $result['released_items']
            ]);
        } else {
            Log::error('Failed to release stock reservations', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'error' => $result['message']
            ]);
        }
    }
    
    /**
     * Process order dispatch (convert reservations to rental out)
     */
    protected function processOrderDispatch(Order $order): void
    {
        $result = $this->inventoryService->processRentalOut($order, auth()->user());
        
        if ($result['success']) {
            Log::info('Rental out processed for order dispatch', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'rented_items' => $result['rented_items']
            ]);
        } else {
            Log::error('Failed to process rental out for order dispatch', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'error' => $result['message']
            ]);
        }
    }
    
    /**
     * Process order completion (handle returns)
     */
    protected function processOrderCompletion(Order $order): void
    {
        // For automatic completion, assume all items returned in good condition
        // In a real system, this might require manual input from staff
        $returnedItems = [];
        
        foreach ($order->orderDetails as $detail) {
            // This is a simplified approach - in practice you'd need to track
            // which specific inventory items were rented out
            $returnedItems[] = [
                'inventory_item_id' => $detail->product_id, // This would need to be the actual inventory item ID
                'quantity' => $detail->quantity,
                'condition' => 'good', // Default assumption
                'notes' => 'Automatic return processing on order completion'
            ];
        }
        
        if (!empty($returnedItems)) {
            $result = $this->inventoryService->processRentalReturn($order, $returnedItems, auth()->user());
            
            if ($result['success']) {
                Log::info('Rental return processed for order completion', [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'returned_items' => $result['returned_items']
                ]);
            } else {
                Log::error('Failed to process rental return for order completion', [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'error' => $result['message']
                ]);
            }
        }
    }
    
    /**
     * Check product availability for order
     */
    public function checkOrderAvailability(array $cartItems, Carbon $startDate, Carbon $endDate): array
    {
        $productIds = collect($cartItems)->pluck('product_id')->unique()->toArray();
        return $this->inventoryService->checkAvailability($productIds, $startDate, $endDate);
    }
    
    /**
     * Manually reserve stock for an order (admin function)
     */
    public function manualReserveStock(Order $order): array
    {
        $order->load('orderDetails.product');
        return $this->inventoryService->reserveStock($order);
    }
    
    /**
     * Manually release stock reservations (admin function)
     */
    public function manualReleaseStock(Order $order): array
    {
        return $this->inventoryService->releaseReservations($order);
    }
    
    /**
     * Process manual rental dispatch (admin function)
     */
    public function manualProcessDispatch(Order $order, ?User $user = null): array
    {
        return $this->inventoryService->processRentalOut($order, $user);
    }
    
    /**
     * Process manual rental return (admin function)
     */
    public function manualProcessReturn(Order $order, array $returnedItems, ?User $user = null): array
    {
        return $this->inventoryService->processRentalReturn($order, $returnedItems, $user);
    }
    

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, string $reason = ''): array
    {
        // Check if cancellation is allowed
        if (!in_array($order->status, ['pending', 'paid'])) {
            return [
                'success' => false,
                'message' => 'This order cannot be cancelled at this stage.'
            ];
        }
        
        // Check if kitchen rental is within cancellation period (e.g., 24 hours before)
        $earliestBookingDate = $order->orderDetails
            ->where('booked_date', '!=', null)
            ->min('booked_date');
        
        if ($earliestBookingDate && Carbon::parse($earliestBookingDate)->subHours(24)->isPast()) {
            return [
                'success' => false,
                'message' => 'Cannot cancel within 24 hours of kitchen rental time.'
            ];
        }
        
        $order->update([
            'status' => 'cancelled',
            'failure_reason' => $reason ?: 'Customer cancellation'
        ]);
        
        // TODO: Process refund if payment was made
        
        return [
            'success' => true,
            'message' => 'Order cancelled successfully.'
        ];
    }

    /**
     * Request refund for an order
     */
    public function requestRefund(Order $order, string $reason): array
    {
        if (!in_array($order->status, ['paid', 'completed', 'delivered'])) {
            return [
                'success' => false,
                'message' => 'Refund can only be requested for paid orders.'
            ];
        }
        
        // Create refund request record (you might want to create a RefundRequest model)
        $order->update([
            'status' => 'refund_requested',
            'failure_reason' => $reason
        ]);
        
        // TODO: Notify admin about refund request
        
        return [
            'success' => true,
            'message' => 'Refund request submitted successfully.'
        ];
    }

    /**
     * Generate invoice PDF
     */
    public function generateInvoicePDF(Order $order)
    {
        $settingsService = app(SettingsService::class);
        $data = [
            'order' => $order,
            'company' => [
                'name' => $settingsService->get('site_name'),
                'address' => $settingsService->get('contact_address'),
                'phone' => $settingsService->get('contact_phone'),
                'email' => $settingsService->get('contact_email')
            ]
        ];
        
        $pdf = Pdf::loadView('pdf.invoice', $data);
        
        return $pdf->download('invoice-' . $order->order_no . '.pdf');
    }

    /**
     * Calculate user profile completion percentage
     */
    public function calculateProfileCompletion(User $user): array
    {
        $fields = [
            'name' => !empty($user->name),
            'email' => !empty($user->email),
            'phone' => !empty($user->phone),
            'address' => !empty($user->address),
            'date_of_birth' => !empty($user->date_of_birth),
            'profile_photo' => !empty($user->profile_photo_path)
        ];
        
        $completedFields = array_filter($fields);
        $completionPercentage = round((count($completedFields) / count($fields)) * 100);
        
        return [
            'percentage' => $completionPercentage,
            'completed_fields' => array_keys($completedFields),
            'missing_fields' => array_keys(array_filter($fields, function ($value) {
                return !$value;
            }))
        ];
    }

    /**
     * Get user loyalty information
     */
    public function getUserLoyaltyInfo(int $userId): array
    {
        $totalOrders = Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed', 'delivered'])
            ->count();
        
        $totalSpent = Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed', 'delivered'])
            ->sum('total');
        
        // Simple loyalty calculation (you can make this more sophisticated)
        $loyaltyPoints = floor($totalSpent); // 1 point per pound spent
        $loyaltyTier = $this->calculateLoyaltyTier($totalSpent);
        
        return [
            'points' => $loyaltyPoints,
            'tier' => $loyaltyTier,
            'total_spent' => $totalSpent,
            'next_tier_threshold' => $this->getNextTierThreshold($loyaltyTier),
            'benefits' => $this->getTierBenefits($loyaltyTier)
        ];
    }

    /**
     * Calculate loyalty tier based on total spending
     */
    private function calculateLoyaltyTier(float $totalSpent): string
    {
        if ($totalSpent >= 5000) return 'Platinum';
        if ($totalSpent >= 2000) return 'Gold';
        if ($totalSpent >= 500) return 'Silver';
        return 'Bronze';
    }

    /**
     * Get next tier threshold
     */
    private function getNextTierThreshold(string $currentTier): ?float
    {
        $thresholds = [
            'Bronze' => 500,
            'Silver' => 2000,
            'Gold' => 5000,
            'Platinum' => null
        ];
        
        return $thresholds[$currentTier];
    }

    /**
     * Get tier benefits
     */
    private function getTierBenefits(string $tier): array
    {
        $benefits = [
            'Bronze' => ['5% discount on orders over £100'],
            'Silver' => ['10% discount on orders over £100', 'Priority booking'],
            'Gold' => ['15% discount on orders over £100', 'Priority booking', 'Free equipment upgrades'],
            'Platinum' => ['20% discount on all orders', 'Priority booking', 'Free equipment upgrades', 'Dedicated support']
        ];
        
        return $benefits[$tier] ?? [];
    }

}