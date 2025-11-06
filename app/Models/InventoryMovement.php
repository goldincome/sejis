<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'order_id',
        'movement_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'from_location',
        'to_location',
        'reference_number',
        'supplier',
        'cost_per_unit',
        'total_cost',
        'reason',
        'notes',
        'metadata',
        'movement_date'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $appends = [
        'movement_type_label',
        'impact_description'
    ];

    // Movement type constants
    public const MOVEMENT_TYPES = [
        'stock_in' => 'Stock In',
        'stock_out' => 'Stock Out',
        'rental_out' => 'Rental Out',
        'rental_return' => 'Rental Return',
        'transfer' => 'Location Transfer',
        'adjustment' => 'Stock Adjustment',
        'damage' => 'Damage',
        'repair' => 'Sent for Repair',
        'repair_return' => 'Returned from Repair',
        'maintenance' => 'Maintenance',
        'disposal' => 'Disposal',
        'reservation' => 'Stock Reserved',
        'unreservation' => 'Reservation Released'
    ];

    // Relationships
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Computed Properties
    public function getMovementTypeLabelAttribute(): string
    {
        return self::MOVEMENT_TYPES[$this->movement_type] ?? ucfirst(str_replace('_', ' ', $this->movement_type));
    }

    public function getImpactDescriptionAttribute(): string
    {
        $impact = $this->quantity_change > 0 ? 'increased' : ($this->quantity_change < 0 ? 'decreased' : 'no change');
        $absChange = abs($this->quantity_change);
        
        if ($this->quantity_change === 0) {
            return "No quantity change - {$this->movement_type_label}";
        }
        
        return "Stock {$impact} by {$absChange} unit(s) - {$this->movement_type_label}";
    }

    // Query Scopes
    public function scopeOfType(Builder $query, string $movementType): Builder
    {
        return $query->where('movement_type', $movementType);
    }

    public function scopeForInventoryItem(Builder $query, int $inventoryItemId): Builder
    {
        return $query->where('inventory_item_id', $inventoryItemId);
    }

    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('movement_date', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('movement_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('movement_date', now()->month)
                    ->whereYear('movement_date', now()->year);
    }

    public function scopeStockIncreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeStockDecreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '<', 0);
    }

    public function scopeWithCost(Builder $query): Builder
    {
        return $query->whereNotNull('total_cost')->where('total_cost', '>', 0);
    }

    public function scopeFromSupplier(Builder $query, string $supplier): Builder
    {
        return $query->where('supplier', 'like', "%{$supplier}%");
    }

    public function scopeAtLocation(Builder $query, string $location): Builder
    {
        return $query->where(function ($q) use ($location) {
            $q->where('from_location', 'like', "%{$location}%")
              ->orWhere('to_location', 'like', "%{$location}%");
        });
    }

    // Static Analysis Methods
    public static function getMovementSummary(int $inventoryItemId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::forInventoryItem($inventoryItemId);
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        $movements = $query->get();
        
        return [
            'total_movements' => $movements->count(),
            'stock_in' => $movements->where('movement_type', 'stock_in')->sum('quantity_change'),
            'stock_out' => abs($movements->where('movement_type', 'stock_out')->sum('quantity_change')),
            'rental_out' => abs($movements->where('movement_type', 'rental_out')->sum('quantity_change')),
            'rental_returns' => $movements->where('movement_type', 'rental_return')->sum('quantity_change'),
            'adjustments' => $movements->where('movement_type', 'adjustment')->sum('quantity_change'),
            'transfers' => $movements->where('movement_type', 'transfer')->count(),
            'maintenance_events' => $movements->where('movement_type', 'maintenance')->count(),
            'total_cost' => $movements->sum('total_cost'),
            'net_change' => $movements->sum('quantity_change')
        ];
    }

    public static function getLocationTransferHistory(string $location, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::ofType('transfer')->atLocation($location);
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        $transfers = $query->with(['inventoryItem.product', 'user'])->get();
        
        return [
            'total_transfers' => $transfers->count(),
            'items_moved_in' => $transfers->where('to_location', 'like', "%{$location}%")->count(),
            'items_moved_out' => $transfers->where('from_location', 'like', "%{$location}%")->count(),
            'recent_transfers' => $transfers->sortByDesc('movement_date')->take(10)->values()->toArray()
        ];
    }

    public static function getUserActivitySummary(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::byUser($userId);
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        $movements = $query->get();
        
        return [
            'total_actions' => $movements->count(),
            'stock_adjustments' => $movements->whereIn('movement_type', ['stock_in', 'stock_out', 'adjustment'])->count(),
            'transfers' => $movements->where('movement_type', 'transfer')->count(),
            'maintenance_actions' => $movements->where('movement_type', 'maintenance')->count(),
            'value_handled' => $movements->sum('total_cost'),
            'items_affected' => $movements->pluck('inventory_item_id')->unique()->count(),
            'movement_types' => $movements->groupBy('movement_type')->map->count()->toArray()
        ];
    }

    public static function getStockMovementTrends(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->where('movement_date', '>=', now()->subDays(30));
        }
        
        $movements = $query->get();
        
        // Group by date for trend analysis
        $dailyTrends = $movements->groupBy(function ($movement) {
            return $movement->movement_date->format('Y-m-d');
        })->map(function ($dayMovements) {
            return [
                'date' => $dayMovements->first()->movement_date->format('Y-m-d'),
                'total_movements' => $dayMovements->count(),
                'stock_in' => $dayMovements->where('movement_type', 'stock_in')->sum('quantity_change'),
                'stock_out' => abs($dayMovements->where('movement_type', 'stock_out')->sum('quantity_change')),
                'rentals' => abs($dayMovements->where('movement_type', 'rental_out')->sum('quantity_change')),
                'returns' => $dayMovements->where('movement_type', 'rental_return')->sum('quantity_change'),
                'net_change' => $dayMovements->sum('quantity_change'),
                'total_value' => $dayMovements->sum('total_cost')
            ];
        })->values();
        
        return [
            'daily_trends' => $dailyTrends->toArray(),
            'period_summary' => [
                'total_movements' => $movements->count(),
                'total_stock_in' => $movements->where('movement_type', 'stock_in')->sum('quantity_change'),
                'total_stock_out' => abs($movements->where('movement_type', 'stock_out')->sum('quantity_change')),
                'total_rentals' => abs($movements->where('movement_type', 'rental_out')->sum('quantity_change')),
                'total_returns' => $movements->where('movement_type', 'rental_return')->sum('quantity_change'),
                'net_stock_change' => $movements->sum('quantity_change'),
                'total_value_moved' => $movements->sum('total_cost'),
                'unique_items_affected' => $movements->pluck('inventory_item_id')->unique()->count(),
                'active_users' => $movements->pluck('user_id')->filter()->unique()->count()
            ]
        ];
    }

    // Validation Methods
    public function validateMovement(): array
    {
        $errors = [];
        
        // Check if quantity change makes sense
        if ($this->quantity_after !== ($this->quantity_before + $this->quantity_change)) {
            $errors[] = 'Quantity calculation mismatch';
        }
        
        // Check for negative stock (except for adjustments)
        if ($this->quantity_after < 0 && !in_array($this->movement_type, ['adjustment', 'stock_out'])) {
            $errors[] = 'Movement would result in negative stock';
        }
        
        // Check required fields based on movement type
        switch ($this->movement_type) {
            case 'transfer':
                if (!$this->from_location || !$this->to_location) {
                    $errors[] = 'Transfer movements require from and to locations';
                }
                break;
                
            case 'stock_in':
                if ($this->quantity_change <= 0) {
                    $errors[] = 'Stock in movements must have positive quantity change';
                }
                break;
                
            case 'stock_out':
            case 'rental_out':
                if ($this->quantity_change >= 0) {
                    $errors[] = 'Stock out/rental out movements must have negative quantity change';
                }
                break;
        }
        
        return $errors;
    }
}
