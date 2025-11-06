<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'serial_number',
        'quantity_on_hand',
        'quantity_reserved',
        'minimum_stock_level',
        'maximum_stock_level',
        'location',
        'zone',
        'shelf_position',
        'condition',
        'last_maintenance_date',
        'next_maintenance_due',
        'maintenance_notes',
        'purchase_cost',
        'current_value',
        'purchase_date',
        'supplier',
        'warranty_period',
        'warranty_expires',
        'is_active',
        'is_rentable',
        'requires_cleaning',
        'requires_inspection',
        'notes'
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_due' => 'date',
        'purchase_date' => 'date',
        'warranty_expires' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_rentable' => 'boolean',
        'requires_cleaning' => 'boolean',
        'requires_inspection' => 'boolean',
    ];

    protected $appends = [
        'quantity_available',
        'is_low_stock',
        'is_out_of_stock',
        'maintenance_status',
        'warranty_status'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function recentMovements(): HasMany
    {
        return $this->movements()->latest()->limit(10);
    }

    // Computed Properties
    public function getQuantityAvailableAttribute(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_available <= $this->minimum_stock_level;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->quantity_available <= 0;
    }

    public function getMaintenanceStatusAttribute(): string
    {
        if (!$this->next_maintenance_due) {
            return 'no_schedule';
        }

        $daysUntilMaintenance = Carbon::now()->diffInDays($this->next_maintenance_due, false);
        
        if ($daysUntilMaintenance < 0) {
            return 'overdue';
        } elseif ($daysUntilMaintenance <= 7) {
            return 'due_soon';
        } elseif ($daysUntilMaintenance <= 30) {
            return 'upcoming';
        } else {
            return 'scheduled';
        }
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_expires) {
            return 'no_warranty';
        }

        $daysUntilExpiry = Carbon::now()->diffInDays($this->warranty_expires, false);
        
        if ($daysUntilExpiry < 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'expiring_soon';
        } else {
            return 'active';
        }
    }

    // Stock Management Methods
    public function adjustStock(int $quantity, string $reason = '', ?User $user = null): bool
    {
        $oldQuantity = $this->quantity_on_hand;
        $newQuantity = $oldQuantity + $quantity;
        
        if ($newQuantity < 0) {
            return false; // Cannot have negative stock
        }

        $this->quantity_on_hand = $newQuantity;
        $this->save();

        // Create movement record
        $this->movements()->create([
            'user_id' => $user?->id,
            'movement_type' => $quantity > 0 ? 'stock_in' : 'stock_out',
            'quantity_change' => $quantity,
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'reason' => $reason ?: ($quantity > 0 ? 'Stock increase' : 'Stock decrease'),
            'movement_date' => now()
        ]);

        return true;
    }

    public function reserveStock(int $quantity, ?int $orderId = null): bool
    {
        if ($this->quantity_available < $quantity) {
            return false; // Not enough available stock
        }

        $this->quantity_reserved += $quantity;
        $this->save();

        // Create movement record
        $this->movements()->create([
            'order_id' => $orderId,
            'movement_type' => 'reservation',
            'quantity_change' => -$quantity,
            'quantity_before' => $this->quantity_on_hand - $this->quantity_reserved + $quantity,
            'quantity_after' => $this->quantity_on_hand - $this->quantity_reserved,
            'reason' => 'Stock reserved for order',
            'movement_date' => now()
        ]);

        return true;
    }

    public function releaseReservation(int $quantity, ?int $orderId = null): bool
    {
        if ($this->quantity_reserved < $quantity) {
            return false; // Cannot release more than reserved
        }

        $this->quantity_reserved -= $quantity;
        $this->save();

        // Create movement record
        $this->movements()->create([
            'order_id' => $orderId,
            'movement_type' => 'unreservation',
            'quantity_change' => $quantity,
            'quantity_before' => $this->quantity_on_hand - $this->quantity_reserved - $quantity,
            'quantity_after' => $this->quantity_on_hand - $this->quantity_reserved,
            'reason' => 'Stock reservation released',
            'movement_date' => now()
        ]);

        return true;
    }

    public function moveToLocation(string $newLocation, ?string $newZone = null, ?User $user = null): bool
    {
        $oldLocation = $this->location;
        $oldZone = $this->zone;
        
        $this->location = $newLocation;
        if ($newZone) {
            $this->zone = $newZone;
        }
        $this->save();

        // Create movement record
        $this->movements()->create([
            'user_id' => $user?->id,
            'movement_type' => 'transfer',
            'quantity_change' => 0,
            'quantity_before' => $this->quantity_on_hand,
            'quantity_after' => $this->quantity_on_hand,
            'from_location' => $oldLocation . ($oldZone ? ' - ' . $oldZone : ''),
            'to_location' => $newLocation . ($newZone ? ' - ' . $newZone : ''),
            'reason' => 'Location transfer',
            'movement_date' => now()
        ]);

        return true;
    }

    public function updateCondition(string $newCondition, string $notes = '', ?User $user = null): bool
    {
        $oldCondition = $this->condition;
        $this->condition = $newCondition;
        $this->notes = $notes ?: $this->notes;
        $this->save();

        // Create movement record for condition change
        $this->movements()->create([
            'user_id' => $user?->id,
            'movement_type' => 'adjustment',
            'quantity_change' => 0,
            'quantity_before' => $this->quantity_on_hand,
            'quantity_after' => $this->quantity_on_hand,
            'reason' => "Condition changed from {$oldCondition} to {$newCondition}",
            'notes' => $notes,
            'metadata' => json_encode([
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition
            ]),
            'movement_date' => now()
        ]);

        return true;
    }

    public function scheduleMaintenance(Carbon $maintenanceDate, string $notes = ''): void
    {
        $this->next_maintenance_due = $maintenanceDate;
        $this->maintenance_notes = $notes;
        $this->save();
    }

    public function completeMaintenance(string $notes = '', ?User $user = null): void
    {
        $this->last_maintenance_date = now();
        $this->next_maintenance_due = null;
        $this->maintenance_notes = $notes;
        $this->save();

        // Create movement record
        $this->movements()->create([
            'user_id' => $user?->id,
            'movement_type' => 'maintenance',
            'quantity_change' => 0,
            'quantity_before' => $this->quantity_on_hand,
            'quantity_after' => $this->quantity_on_hand,
            'reason' => 'Maintenance completed',
            'notes' => $notes,
            'movement_date' => now()
        ]);
    }

    // Query Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRentable(Builder $query): Builder
    {
        return $query->where('is_rentable', true)->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) <= minimum_stock_level');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) <= 0');
    }

    public function scopeAtLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    public function scopeInCondition(Builder $query, string $condition): Builder
    {
        return $query->where('condition', $condition);
    }

    public function scopeMaintenanceDue(Builder $query, int $daysAhead = 7): Builder
    {
        return $query->where('next_maintenance_due', '<=', now()->addDays($daysAhead));
    }

    public function scopeWarrantyExpiring(Builder $query, int $daysAhead = 30): Builder
    {
        return $query->where('warranty_expires', '<=', now()->addDays($daysAhead))
                    ->where('warranty_expires', '>', now());
    }

    // Static Methods
    public static function generateSKU(Product $product): string
    {
        $prefix = strtoupper(substr($product->name, 0, 3));
        $categoryCode = str_pad($product->category_id, 2, '0', STR_PAD_LEFT);
        $productCode = str_pad($product->id, 4, '0', STR_PAD_LEFT);
        $itemNumber = str_pad(static::where('product_id', $product->id)->count() + 1, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$categoryCode}-{$productCode}-{$itemNumber}";
    }

    public static function getTotalStockValue(): float
    {
        return static::sum(DB::raw('quantity_on_hand * COALESCE(current_value, purchase_cost, 0)'));
    }

    public static function getLowStockCount(): int
    {
        return static::lowStock()->count();
    }

    public static function getOutOfStockCount(): int
    {
        return static::outOfStock()->count();
    }
}
