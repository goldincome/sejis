<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory dashboard
     */
    public function index(Request $request)
    {
        $stats = $this->inventoryService->getDashboardStats();
        $lowStockAlerts = $this->inventoryService->getLowStockAlerts();
        
        // Recent movements
        $recentMovements = InventoryMovement::with(['inventoryItem.product', 'user'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Dashboard alerts for immediate attention
        $dashboardAlerts = $this->getDashboardAlerts();
        
        return view('admin.inventory.index', compact(
            'stats',
            'lowStockAlerts', 
            'recentMovements',
            'dashboardAlerts'
        ));
    }

    /**
     * List all inventory items
     */
    public function items(Request $request)
    {
        $query = InventoryItem::with(['product.category'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('sku', 'like', "%{$request->search}%")
                  ->orWhereHas('product', function ($productQuery) use ($request) {
                      $productQuery->where('name', 'like', "%{$request->search}%");
                  });
            })
            ->when($request->location, function ($q) use ($request) {
                $q->where('location', $request->location);
            })
            ->when($request->condition, function ($q) use ($request) {
                $q->where('condition', $request->condition);
            })
            ->when($request->status, function ($q) use ($request) {
                switch ($request->status) {
                    case 'low_stock':
                        $q->lowStock();
                        break;
                    case 'out_of_stock':
                        $q->outOfStock();
                        break;
                    case 'maintenance_due':
                        $q->maintenanceDue();
                        break;
                    case 'warranty_expiring':
                        $q->warrantyExpiring();
                        break;
                }
            });

        $items = $query->latest()->paginate(20);
        
        // Get filter options
        $locations = InventoryItem::distinct()->pluck('location')->filter();
        $conditions = ['excellent', 'good', 'fair', 'poor', 'needs_repair', 'out_of_service'];
        
        return view('admin.inventory.items', compact(
            'items',
            'locations',
            'conditions'
        ));
    }

    /**
     * Show inventory item details
     */
    public function show(InventoryItem $inventoryItem)
    {
        $inventoryItem->load([
            'product.category',
            'movements' => function ($query) {
                $query->with('user', 'order')->latest()->limit(20);
            }
        ]);
        
        return view('admin.inventory.show', compact('inventoryItem'));
    }

    /**
     * Show create inventory item form
     */
    public function create()
    {
        $products = Product::active()->with('category')->get();
        $locations = InventoryItem::distinct()->pluck('location')->filter();
        
        return view('admin.inventory.create', compact('products', 'locations'));
    }

    /**
     * Store new inventory item
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:excellent,good,fair,poor,needs_repair,out_of_service',
            'minimum_stock_level' => 'required|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_period' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        try {
            $result = $this->inventoryService->addStock(
                $request->product_id,
                $request->quantity,
                $request->except(['_token', 'product_id', 'quantity']),
                auth()->user()
            );

            if ($result['success']) {
                return redirect()->route('admin.inventory.items')
                    ->with('success', $result['message']);
            } else {
                return back()->withErrors($result['message'])->withInput();
            }
        } catch (Exception $e) {
            Log::error('Failed to create inventory item', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->withErrors('Failed to create inventory item')->withInput();
        }
    }

    /**
     * Show edit inventory item form
     */
    public function edit(InventoryItem $inventoryItem)
    {
        $products = Product::active()->with('category')->get();
        $locations = InventoryItem::distinct()->pluck('location')->filter();
        
        return view('admin.inventory.edit', compact('inventoryItem', 'products', 'locations'));
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'minimum_stock_level' => 'required|integer|min:0',
            'maximum_stock_level' => 'nullable|integer|min:0',
            'location' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'condition' => 'required|in:excellent,good,fair,poor,needs_repair,out_of_service',
            'current_value' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'warranty_period' => 'nullable|string|max:255',
            'warranty_expires' => 'nullable|date',
            'is_active' => 'boolean',
            'is_rentable' => 'boolean',
            'requires_cleaning' => 'boolean',
            'requires_inspection' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        try {
            $originalCondition = $inventoryItem->condition;
            $originalLocation = $inventoryItem->location;
            
            $inventoryItem->fill($request->validated());
            $inventoryItem->save();
            
            // Log condition change if it occurred
            if ($originalCondition !== $inventoryItem->condition) {
                $inventoryItem->updateCondition(
                    $inventoryItem->condition,
                    $request->notes ?? "Condition updated via admin panel",
                    auth()->user()
                );
            }
            
            // Log location change if it occurred
            if ($originalLocation !== $inventoryItem->location) {
                $inventoryItem->moveToLocation(
                    $inventoryItem->location,
                    $inventoryItem->zone,
                    auth()->user()
                );
            }

            return redirect()->route('admin.inventory.show', $inventoryItem)
                ->with('success', 'Inventory item updated successfully');
                
        } catch (Exception $e) {
            Log::error('Failed to update inventory item', [
                'inventory_item_id' => $inventoryItem->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->withErrors('Failed to update inventory item')->withInput();
        }
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $request->validate([
            'quantity_change' => 'required|integer',
            'reason' => 'required|string|max:255'
        ]);

        try {
            $success = $inventoryItem->adjustStock(
                $request->quantity_change,
                $request->reason,
                auth()->user()
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock adjusted successfully',
                    'new_quantity' => $inventoryItem->fresh()->quantity_on_hand
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock adjustment failed - would result in negative stock'
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Failed to adjust stock', [
                'inventory_item_id' => $inventoryItem->id,
                'quantity_change' => $request->quantity_change,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Stock adjustment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer item to new location
     */
    public function transfer(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $request->validate([
            'new_location' => 'required|string|max:255',
            'new_zone' => 'nullable|string|max:255'
        ]);

        try {
            $success = $inventoryItem->moveToLocation(
                $request->new_location,
                $request->new_zone,
                auth()->user()
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item transferred successfully',
                    'new_location' => $inventoryItem->fresh()->location
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer failed'
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Failed to transfer item', [
                'inventory_item_id' => $inventoryItem->id,
                'new_location' => $request->new_location,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule maintenance
     */
    public function scheduleMaintenance(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $request->validate([
            'maintenance_date' => 'required|date|after:today',
            'notes' => 'nullable|string'
        ]);

        try {
            $inventoryItem->scheduleMaintenance(
                Carbon::parse($request->maintenance_date),
                $request->notes ?? ''
            );

            return response()->json([
                'success' => true,
                'message' => 'Maintenance scheduled successfully',
                'maintenance_date' => $inventoryItem->fresh()->next_maintenance_due->format('Y-m-d')
            ]);
        } catch (Exception $e) {
            Log::error('Failed to schedule maintenance', [
                'inventory_item_id' => $inventoryItem->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule maintenance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete maintenance
     */
    public function completeMaintenance(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            $inventoryItem->completeMaintenance(
                $request->notes ?? 'Maintenance completed',
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Maintenance completed successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to complete maintenance', [
                'inventory_item_id' => $inventoryItem->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete maintenance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory movements
     */
    public function movements(Request $request)
    {
        $query = InventoryMovement::with(['inventoryItem.product', 'user', 'order'])
            ->when($request->inventory_item_id, function ($q) use ($request) {
                $q->where('inventory_item_id', $request->inventory_item_id);
            })
            ->when($request->movement_type, function ($q) use ($request) {
                $q->where('movement_type', $request->movement_type);
            })
            ->when($request->user_id, function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })
            ->when($request->date_from, function ($q) use ($request) {
                $q->where('movement_date', '>=', Carbon::parse($request->date_from));
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->where('movement_date', '<=', Carbon::parse($request->date_to)->endOfDay());
            });

        $movements = $query->latest('movement_date')->paginate(20);
        
        // Get filter options
        $movementTypes = InventoryMovement::MOVEMENT_TYPES;
        $inventoryItems = InventoryItem::with('product')->get()->pluck('product.name', 'id');
        
        return view('admin.inventory.movements', compact(
            'movements',
            'movementTypes',
            'inventoryItems'
        ));
    }

    /**
     * Get low stock alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        try {
            $alerts = $this->inventoryService->getLowStockAlerts();
            
            return response()->json([
                'success' => true,
                'alerts' => $alerts,
                'count' => count($alerts)
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get low stock alerts', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send low stock alerts via email
     */
    public function sendLowStockAlerts(): JsonResponse
    {
        try {
            $success = $this->inventoryService->sendLowStockAlerts();
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Low stock alerts sent successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send low stock alerts'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $stats = $this->inventoryService->getDashboardStats();
            
            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check product availability for date range
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        try {
            $availability = $this->inventoryService->checkAvailability(
                $request->product_ids,
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            );
            
            return response()->json([
                'success' => true,
                'availability' => $availability
            ]);
        } catch (Exception $e) {
            Log::error('Failed to check availability', [
                'product_ids' => $request->product_ids,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update inventory items
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer|exists:inventory_items,id',
            'action' => 'required|in:activate,deactivate,mark_cleaning,mark_inspection,set_location',
            'value' => 'nullable|string'
        ]);

        try {
            $updated = 0;
            $items = InventoryItem::whereIn('id', $request->item_ids)->get();
            
            foreach ($items as $item) {
                switch ($request->action) {
                    case 'activate':
                        $item->update(['is_active' => true, 'is_rentable' => true]);
                        $updated++;
                        break;
                        
                    case 'deactivate':
                        $item->update(['is_active' => false, 'is_rentable' => false]);
                        $updated++;
                        break;
                        
                    case 'mark_cleaning':
                        $item->update(['requires_cleaning' => true]);
                        $updated++;
                        break;
                        
                    case 'mark_inspection':
                        $item->update(['requires_inspection' => true]);
                        $updated++;
                        break;
                        
                    case 'set_location':
                        if ($request->value) {
                            $item->moveToLocation($request->value, null, auth()->user());
                            $updated++;
                        }
                        break;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} items successfully",
                'updated_count' => $updated
            ]);
            
        } catch (Exception $e) {
            Log::error('Bulk update failed', [
                'action' => $request->action,
                'item_ids' => $request->item_ids,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get dashboard alerts for immediate attention
     */
    private function getDashboardAlerts(): array
    {
        $alerts = [];
        
        // Critical stock alerts (out of stock)
        $outOfStockCount = InventoryItem::outOfStock()->active()->count();
        if ($outOfStockCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'Critical Stock Alert',
                'message' => "{$outOfStockCount} items are completely out of stock",
                'action_text' => 'View Items',
                'action_url' => route('admin.inventory.items', ['status' => 'out_of_stock']),
                'priority' => 'high'
            ];
        }
        
        // Low stock warnings
        $lowStockCount = InventoryItem::lowStock()->active()->count() - $outOfStockCount;
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Low Stock Warning',
                'message' => "{$lowStockCount} items are below minimum stock levels",
                'action_text' => 'Review Items',
                'action_url' => route('admin.inventory.items', ['status' => 'low_stock']),
                'priority' => 'medium'
            ];
        }
        
        // Maintenance due alerts
        $maintenanceDueCount = InventoryItem::where('next_maintenance_due', '<=', now())
            ->where('next_maintenance_due', '!=', null)
            ->active()
            ->count();
        if ($maintenanceDueCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fas fa-wrench',
                'title' => 'Maintenance Due',
                'message' => "{$maintenanceDueCount} items require maintenance",
                'action_text' => 'Schedule Maintenance',
                'action_url' => route('admin.inventory.items', ['status' => 'maintenance_due']),
                'priority' => 'medium'
            ];
        }
        
        // Items requiring cleaning/inspection
        $cleaningRequiredCount = InventoryItem::where('requires_cleaning', true)
            ->orWhere('requires_inspection', true)
            ->active()
            ->count();
        if ($cleaningRequiredCount > 0) {
            $alerts[] = [
                'type' => 'secondary',
                'icon' => 'fas fa-broom',
                'title' => 'Cleaning/Inspection Required',
                'message' => "{$cleaningRequiredCount} items need cleaning or inspection",
                'action_text' => 'View Items',
                'action_url' => route('admin.inventory.items', ['requires_attention' => 'true']),
                'priority' => 'low'
            ];
        }
        
        // Warranty expiring alerts
        $warrantyExpiringCount = InventoryItem::where('warranty_expires', '<=', now()->addDays(30))
            ->where('warranty_expires', '>', now())
            ->active()
            ->count();
        if ($warrantyExpiringCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-shield-alt',
                'title' => 'Warranty Expiring',
                'message' => "{$warrantyExpiringCount} items have warranties expiring within 30 days",
                'action_text' => 'Review Warranties',
                'action_url' => route('admin.inventory.items', ['status' => 'warranty_expiring']),
                'priority' => 'medium'
            ];
        }
        
        // Sort alerts by priority
        $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
        usort($alerts, function ($a, $b) use ($priorityOrder) {
            return $priorityOrder[$b['priority']] <=> $priorityOrder[$a['priority']];
        });
        
        return $alerts;
    }
}
