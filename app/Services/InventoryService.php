<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use App\Mail\LowStockAlertMail;
use App\Jobs\SendNotificationEmailJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;

class InventoryService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Check availability of products for a given date range
     */
    public function checkAvailability(array $productIds, Carbon $startDate, Carbon $endDate): array
    {
        $availability = [];
        
        foreach ($productIds as $productId) {
            $inventoryItems = InventoryItem::where('product_id', $productId)
                ->rentable()
                ->get();
            
            $totalAvailable = 0;
            $itemDetails = [];
            
            foreach ($inventoryItems as $item) {
                // Check if item is already rented during this period
                $conflictingRentals = $this->getConflictingRentals($item->id, $startDate, $endDate);
                
                if ($conflictingRentals === 0) {
                    $totalAvailable += $item->quantity_available;
                    $itemDetails[] = [
                        'id' => $item->id,
                        'sku' => $item->sku,
                        'condition' => $item->condition,
                        'location' => $item->location,
                        'available_quantity' => $item->quantity_available
                    ];
                }
            }
            
            $availability[$productId] = [
                'product_id' => $productId,
                'total_available' => $totalAvailable,
                'available_items' => $itemDetails,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ];
        }
        
        return $availability;
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock(Order $order): array
    {
        $results = [];
        $reservationErrors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($order->orderDetails as $detail) {
                $product = $detail->product;
                $quantityNeeded = $detail->quantity;
                
                // Find best available inventory items
                $availableItems = $this->findBestAvailableItems($product->id, $quantityNeeded);
                
                if ($availableItems['total_available'] < $quantityNeeded) {
                    $reservationErrors[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity_needed' => $quantityNeeded,
                        'quantity_available' => $availableItems['total_available'],
                        'error' => 'Insufficient stock available'
                    ];
                    continue;
                }
                
                // Reserve stock from available items
                $remainingToReserve = $quantityNeeded;
                $reservedItems = [];
                
                foreach ($availableItems['items'] as $item) {
                    if ($remainingToReserve <= 0) break;
                    
                    $reserveFromThisItem = min($remainingToReserve, $item->quantity_available);
                    
                    if ($item->reserveStock($reserveFromThisItem, $order->id)) {
                        $reservedItems[] = [
                            'inventory_item_id' => $item->id,
                            'sku' => $item->sku,
                            'quantity_reserved' => $reserveFromThisItem
                        ];
                        $remainingToReserve -= $reserveFromThisItem;
                    }
                }
                
                $results[$product->id] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_requested' => $quantityNeeded,
                    'quantity_reserved' => $quantityNeeded - $remainingToReserve,
                    'reserved_items' => $reservedItems,
                    'success' => $remainingToReserve === 0
                ];
            }
            
            if (empty($reservationErrors)) {
                DB::commit();
                
                // Log successful reservation
                Log::info('Stock reserved successfully for order', [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'results' => $results
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Stock reserved successfully',
                    'reservations' => $results
                ];
            } else {
                DB::rollBack();
                
                return [
                    'success' => false,
                    'message' => 'Unable to reserve required stock',
                    'errors' => $reservationErrors,
                    'partial_results' => $results
                ];
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Stock reservation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Stock reservation failed: ' . $e->getMessage(),
                'errors' => $reservationErrors
            ];
        }
    }

    /**
     * Release stock reservations (e.g., when order is cancelled)
     */
    public function releaseReservations(Order $order): array
    {
        $results = [];
        
        DB::beginTransaction();
        
        try {
            // Find all reservation movements for this order
            $reservationMovements = InventoryMovement::where('order_id', $order->id)
                ->where('movement_type', 'reservation')
                ->with('inventoryItem')
                ->get();
            
            foreach ($reservationMovements as $movement) {
                $item = $movement->inventoryItem;
                $quantityToRelease = abs($movement->quantity_change);
                
                if ($item->releaseReservation($quantityToRelease, $order->id)) {
                    $results[] = [
                        'inventory_item_id' => $item->id,
                        'sku' => $item->sku,
                        'quantity_released' => $quantityToRelease
                    ];
                }
            }
            
            DB::commit();
            
            Log::info('Stock reservations released for order', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'items_released' => count($results)
            ]);
            
            return [
                'success' => true,
                'message' => 'Reservations released successfully',
                'released_items' => $results
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to release reservations', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to release reservations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process rental out (when items are picked up)
     */
    public function processRentalOut(Order $order, ?User $user = null): array
    {
        $results = [];
        
        DB::beginTransaction();
        
        try {
            // Find all reserved items for this order
            $reservationMovements = InventoryMovement::where('order_id', $order->id)
                ->where('movement_type', 'reservation')
                ->with('inventoryItem')
                ->get();
            
            foreach ($reservationMovements as $movement) {
                $item = $movement->inventoryItem;
                $quantityToRent = abs($movement->quantity_change);
                
                // Convert reservation to rental out
                $item->adjustStock(-$quantityToRent, 'Rental out for order ' . $order->reference, $user);
                
                // Release the reservation
                $item->releaseReservation($quantityToRent, $order->id);
                
                // Create rental out movement
                $item->movements()->create([
                    'user_id' => $user?->id,
                    'order_id' => $order->id,
                    'movement_type' => 'rental_out',
                    'quantity_change' => -$quantityToRent,
                    'quantity_before' => $item->quantity_on_hand + $quantityToRent,
                    'quantity_after' => $item->quantity_on_hand,
                    'reason' => 'Item rented out for order ' . $order->reference,
                    'movement_date' => now()
                ]);
                
                $results[] = [
                    'inventory_item_id' => $item->id,
                    'sku' => $item->sku,
                    'quantity_rented' => $quantityToRent
                ];
            }
            
            DB::commit();
            
            Log::info('Rental out processed successfully', [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'items_rented' => count($results)
            ]);
            
            return [
                'success' => true,
                'message' => 'Rental out processed successfully',
                'rented_items' => $results
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process rental out', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process rental out: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process rental return (when items are returned)
     */
    public function processRentalReturn(Order $order, array $returnedItems, ?User $user = null): array
    {
        $results = [];
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($returnedItems as $returnedItem) {
                $inventoryItem = InventoryItem::find($returnedItem['inventory_item_id']);
                
                if (!$inventoryItem) {
                    $errors[] = "Inventory item {$returnedItem['inventory_item_id']} not found";
                    continue;
                }
                
                $quantityReturned = $returnedItem['quantity'];
                $condition = $returnedItem['condition'] ?? $inventoryItem->condition;
                $notes = $returnedItem['notes'] ?? '';
                
                // Update stock
                $inventoryItem->adjustStock($quantityReturned, 'Rental return from order ' . $order->reference, $user);
                
                // Update condition if changed
                if ($condition !== $inventoryItem->condition) {
                    $inventoryItem->updateCondition($condition, $notes, $user);
                }
                
                // Create rental return movement
                $inventoryItem->movements()->create([
                    'user_id' => $user?->id,
                    'order_id' => $order->id,
                    'movement_type' => 'rental_return',
                    'quantity_change' => $quantityReturned,
                    'quantity_before' => $inventoryItem->quantity_on_hand - $quantityReturned,
                    'quantity_after' => $inventoryItem->quantity_on_hand,
                    'reason' => 'Item returned from order ' . $order->reference,
                    'notes' => $notes,
                    'metadata' => json_encode([
                        'return_condition' => $condition,
                        'previous_condition' => $inventoryItem->getOriginal('condition')
                    ]),
                    'movement_date' => now()
                ]);
                
                // Mark for cleaning/inspection if needed
                if (in_array($condition, ['fair', 'poor']) || !empty($notes)) {
                    $inventoryItem->requires_cleaning = true;
                    $inventoryItem->requires_inspection = true;
                    $inventoryItem->save();
                }
                
                $results[] = [
                    'inventory_item_id' => $inventoryItem->id,
                    'sku' => $inventoryItem->sku,
                    'quantity_returned' => $quantityReturned,
                    'condition' => $condition,
                    'requires_cleaning' => $inventoryItem->requires_cleaning,
                    'requires_inspection' => $inventoryItem->requires_inspection
                ];
            }
            
            if (empty($errors)) {
                DB::commit();
                
                Log::info('Rental return processed successfully', [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'items_returned' => count($results)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Rental return processed successfully',
                    'returned_items' => $results
                ];
            } else {
                DB::rollBack();
                
                return [
                    'success' => false,
                    'message' => 'Rental return failed with errors',
                    'errors' => $errors,
                    'partial_results' => $results
                ];
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process rental return', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process rental return: ' . $e->getMessage(),
                'errors' => $errors
            ];
        }
    }

    /**
     * Add new stock to inventory
     */
    public function addStock(int $productId, int $quantity, array $itemDetails = [], ?User $user = null): array
    {
        DB::beginTransaction();
        
        try {
            $product = Product::findOrFail($productId);
            $results = [];
            
            // Find existing inventory item or create new one
            $inventoryItem = InventoryItem::where('product_id', $productId)
                ->where('location', $itemDetails['location'] ?? 'Main Warehouse')
                ->where('condition', $itemDetails['condition'] ?? 'good')
                ->first();
            
            if ($inventoryItem) {
                // Add to existing item
                $inventoryItem->adjustStock($quantity, $itemDetails['reason'] ?? 'Stock addition', $user);
                
                $results[] = [
                    'inventory_item_id' => $inventoryItem->id,
                    'sku' => $inventoryItem->sku,
                    'action' => 'updated_existing',
                    'quantity_added' => $quantity,
                    'new_total' => $inventoryItem->quantity_on_hand
                ];
            } else {
                // Create new inventory item
                $inventoryItem = InventoryItem::create([
                    'product_id' => $productId,
                    'sku' => $itemDetails['sku'] ?? InventoryItem::generateSKU($product),
                    'serial_number' => $itemDetails['serial_number'] ?? null,
                    'quantity_on_hand' => $quantity,
                    'minimum_stock_level' => $itemDetails['minimum_stock_level'] ?? 1,
                    'maximum_stock_level' => $itemDetails['maximum_stock_level'] ?? null,
                    'location' => $itemDetails['location'] ?? 'Main Warehouse',
                    'zone' => $itemDetails['zone'] ?? null,
                    'condition' => $itemDetails['condition'] ?? 'good',
                    'purchase_cost' => $itemDetails['purchase_cost'] ?? null,
                    'current_value' => $itemDetails['current_value'] ?? null,
                    'purchase_date' => $itemDetails['purchase_date'] ?? now(),
                    'supplier' => $itemDetails['supplier'] ?? null,
                    'warranty_period' => $itemDetails['warranty_period'] ?? null,
                    'warranty_expires' => $itemDetails['warranty_expires'] ?? null,
                    'notes' => $itemDetails['notes'] ?? null
                ]);
                
                // Create initial stock movement
                $inventoryItem->movements()->create([
                    'user_id' => $user?->id,
                    'movement_type' => 'stock_in',
                    'quantity_change' => $quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $quantity,
                    'supplier' => $itemDetails['supplier'] ?? null,
                    'cost_per_unit' => $itemDetails['purchase_cost'] ?? null,
                    'total_cost' => ($itemDetails['purchase_cost'] ?? 0) * $quantity,
                    'reference_number' => $itemDetails['reference_number'] ?? null,
                    'reason' => $itemDetails['reason'] ?? 'Initial stock addition',
                    'movement_date' => now()
                ]);
                
                $results[] = [
                    'inventory_item_id' => $inventoryItem->id,
                    'sku' => $inventoryItem->sku,
                    'action' => 'created_new',
                    'quantity_added' => $quantity,
                    'total_quantity' => $inventoryItem->quantity_on_hand
                ];
            }
            
            DB::commit();
            
            Log::info('Stock added successfully', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'user_id' => $user?->id,
                'results' => $results
            ]);
            
            return [
                'success' => true,
                'message' => 'Stock added successfully',
                'items' => $results
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to add stock', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to add stock: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(): array
    {
        $lowStockItems = InventoryItem::lowStock()
            ->with(['product.category'])
            ->active()
            ->get();
        
        $alerts = [];
        
        foreach ($lowStockItems as $item) {
            $alerts[] = [
                'inventory_item_id' => $item->id,
                'sku' => $item->sku,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'category' => $item->product->category->name ?? 'Uncategorized',
                'current_stock' => $item->quantity_available,
                'minimum_level' => $item->minimum_stock_level,
                'location' => $item->location,
                'condition' => $item->condition,
                'alert_level' => $this->getAlertLevel($item->quantity_available, $item->minimum_stock_level)
            ];
        }
        
        // Sort by alert level (critical first)
        usort($alerts, function ($a, $b) {
            $levels = ['critical' => 3, 'warning' => 2, 'low' => 1];
            return $levels[$b['alert_level']] <=> $levels[$a['alert_level']];
        });
        
        return $alerts;
    }

    /**
     * Get inventory dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $cacheKey = 'inventory_dashboard_stats';
        
        return Cache::remember($cacheKey, 300, function () {
            $totalItems = InventoryItem::active()->count();
            $totalStockValue = InventoryItem::getTotalStockValue();
            $lowStockCount = InventoryItem::getLowStockCount();
            $outOfStockCount = InventoryItem::getOutOfStockCount();
            
            // Movement statistics for today
            $todayMovements = InventoryMovement::today()->count();
            $todayStockIn = InventoryMovement::today()->ofType('stock_in')->sum('quantity_change');
            $todayStockOut = abs(InventoryMovement::today()->ofType('stock_out')->sum('quantity_change'));
            $todayRentals = abs(InventoryMovement::today()->ofType('rental_out')->sum('quantity_change'));
            
            // Maintenance and warranty alerts
            $maintenanceDue = InventoryItem::maintenanceDue()->count();
            $warrantyExpiring = InventoryItem::warrantyExpiring()->count();
            
            return [
                'inventory_summary' => [
                    'total_items' => $totalItems,
                    'total_stock_value' => $totalStockValue,
                    'low_stock_count' => $lowStockCount,
                    'out_of_stock_count' => $outOfStockCount,
                    'stock_health_percentage' => $totalItems > 0 ? round((($totalItems - $lowStockCount - $outOfStockCount) / $totalItems) * 100, 1) : 0
                ],
                'daily_activity' => [
                    'total_movements' => $todayMovements,
                    'stock_in' => $todayStockIn,
                    'stock_out' => $todayStockOut,
                    'rentals' => $todayRentals,
                    'net_change' => $todayStockIn - $todayStockOut - $todayRentals
                ],
                'alerts' => [
                    'maintenance_due' => $maintenanceDue,
                    'warranty_expiring' => $warrantyExpiring,
                    'requires_cleaning' => InventoryItem::where('requires_cleaning', true)->count(),
                    'requires_inspection' => InventoryItem::where('requires_inspection', true)->count()
                ]
            ];
        });
    }

    /**
     * Send low stock alerts via email
     */
    public function sendLowStockAlerts(array $alertSettings = []): bool
    {
        try {
            $lowStockItems = $this->getLowStockItemsCollection();
            
            if ($lowStockItems->isEmpty()) {
                Log::info('No low stock items found - skipping alert emails');
                return true; // No alerts to send
            }
            
            // Filter for items that actually need alerts based on settings
            $filteredItems = $this->filterItemsForAlerts($lowStockItems, $alertSettings);
            
            if ($filteredItems->isEmpty()) {
                Log::info('No items meet alert criteria - skipping alert emails');
                return true; // No items meet criteria
            }
            
            // Check if we should send alerts based on frequency settings
            if (!$this->shouldSendAlertsNow($alertSettings)) {
                Log::info('Alert frequency check failed - skipping alert emails');
                return true; // Not time to send alerts
            }
            
            // Create and queue the low stock alert email
            $lowStockAlert = new LowStockAlertMail($filteredItems, $alertSettings);
            
            // Send via queue for better performance
            SendNotificationEmailJob::dispatch(
                $lowStockAlert,
                $lowStockAlert->getQueuePriority()
            )->onQueue($lowStockAlert->determineUrgencyLevel() === 'critical' ? 'high-priority' : 'default');
            
            // Update last alert sent timestamp
            $this->updateLastAlertSentTime();
            
            Log::info('Low stock alert email queued successfully', [
                'item_count' => $filteredItems->count(),
                'urgency_level' => $lowStockAlert->determineUrgencyLevel(),
                'recipients' => count($lowStockAlert->getRecipients())
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('Failed to send low stock alerts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Get low stock items as a collection (for email alerts)
     */
    public function getLowStockItemsCollection(): \Illuminate\Support\Collection
    {
        return InventoryItem::lowStock()
            ->with(['product.category'])
            ->active()
            ->orderBy('quantity_on_hand', 'asc') // Most critical first
            ->orderBy('minimum_stock_level', 'desc')
            ->get();
    }
    
    /**
     * Filter items for alerts based on settings
     */
    private function filterItemsForAlerts(\Illuminate\Support\Collection $items, array $settings): \Illuminate\Support\Collection
    {
        $alertLevel = $settings['alert_level'] ?? 'all';
        $includeOutOfStock = $settings['include_out_of_stock'] ?? true;
        $includeLowStock = $settings['include_low_stock'] ?? true;
        $includeMaintenanceDue = $settings['include_maintenance_due'] ?? false;
        
        return $items->filter(function ($item) use ($alertLevel, $includeOutOfStock, $includeLowStock, $includeMaintenanceDue) {
            // Filter out of stock items
            if (!$includeOutOfStock && $item->quantity_on_hand <= 0) {
                return false;
            }
            
            // Filter low stock items
            if (!$includeLowStock && $item->quantity_on_hand > 0 && $item->isLowStock()) {
                return false;
            }
            
            // Filter maintenance due items
            if ($includeMaintenanceDue && !$item->isMaintenanceDue()) {
                return false;
            }
            
            // Filter by alert level
            if ($alertLevel === 'critical_only') {
                return $item->quantity_on_hand <= 0 || 
                       ($item->minimum_stock_level && $item->quantity_on_hand <= $item->minimum_stock_level * 0.25);
            }
            
            return true;
        });
    }
    
    /**
     * Check if alerts should be sent based on frequency settings
     */
    private function shouldSendAlertsNow(array $settings): bool
    {
        $frequency = $settings['notification_frequency'] ?? 'daily';
        $lastSent = Cache::get('last_low_stock_alert_sent');
        
        if (!$lastSent) {
            return true; // Never sent before
        }
        
        $lastSentTime = Carbon::parse($lastSent);
        $now = now();
        
        return match ($frequency) {
            'immediate' => true, // Always send
            'hourly' => $now->diffInHours($lastSentTime) >= 1,
            'daily' => $now->diffInDays($lastSentTime) >= 1,
            'weekly' => $now->diffInWeeks($lastSentTime) >= 1,
            'manual' => false, // Only manual sending
            default => $now->diffInDays($lastSentTime) >= 1
        };
    }
    
    /**
     * Update the last alert sent timestamp
     */
    private function updateLastAlertSentTime(): void
    {
        Cache::put('last_low_stock_alert_sent', now()->toISOString(), now()->addDays(30));
    }
    
    /**
     * Force send low stock alerts (bypass frequency check)
     */
    public function forceSendLowStockAlerts(array $alertSettings = []): bool
    {
        $alertSettings['notification_frequency'] = 'immediate';
        return $this->sendLowStockAlerts($alertSettings);
    }
    
    /**
     * Get low stock alert settings from configuration
     */
    public function getLowStockAlertSettings(): array
    {
        return [
            'notification_frequency' => setting('low_stock_alert_frequency', 'daily'),
            'alert_level' => setting('low_stock_alert_level', 'all'),
            'include_out_of_stock' => setting('include_out_of_stock_alerts', true),
            'include_low_stock' => setting('include_low_stock_alerts', true),
            'include_maintenance_due' => setting('include_maintenance_due_alerts', false),
            'minimum_items_threshold' => setting('minimum_items_for_alert', 1),
            'send_to_managers' => setting('send_alerts_to_managers', true),
            'send_to_admins' => setting('send_alerts_to_admins', true),
            'escalate_critical' => setting('escalate_critical_alerts', true)
        ];
    }

    /**
     * Helper method to find best available inventory items
     */
    private function findBestAvailableItems(int $productId, int $quantityNeeded): array
    {
        $items = InventoryItem::where('product_id', $productId)
            ->rentable()
            ->where('quantity_available', '>', 0)
            ->orderBy('condition', 'desc') // Best condition first
            ->orderBy('quantity_available', 'desc') // Highest quantity first
            ->get();
        
        $totalAvailable = $items->sum('quantity_available');
        
        return [
            'items' => $items,
            'total_available' => $totalAvailable
        ];
    }

    /**
     * Helper method to check for conflicting rentals
     */
    private function getConflictingRentals(int $inventoryItemId, Carbon $startDate, Carbon $endDate): int
    {
        // This would need to be implemented based on your rental tracking system
        // For now, return 0 (no conflicts)
        return 0;
    }

    /**
     * Helper method to determine alert level
     */
    private function getAlertLevel(int $currentStock, int $minimumLevel): string
    {
        if ($currentStock === 0) {
            return 'critical';
        } elseif ($currentStock <= $minimumLevel * 0.5) {
            return 'warning';
        } else {
            return 'low';
        }
    }
}