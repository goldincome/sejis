<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseCost = $this->faker->randomFloat(2, 20, 200);
        $currentValue = $purchaseCost * $this->faker->randomFloat(2, 0.8, 1.2); // ±20% of purchase cost
        
        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'serial_number' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{8}'),
            'quantity_on_hand' => $this->faker->numberBetween(0, 50),
            'quantity_reserved' => 0,
            'minimum_stock_level' => $this->faker->numberBetween(1, 5),
            'maximum_stock_level' => $this->faker->numberBetween(20, 100),
            'location' => $this->faker->randomElement(['Main Warehouse', 'Storage Room A', 'Storage Room B', 'Outdoor Storage']),
            'zone' => $this->faker->optional()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'shelf_position' => $this->faker->optional()->regexify('[A-D][1-9]'),
            'condition' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor', 'needs_repair']),
            'last_maintenance_date' => $this->faker->optional()->dateTimeBetween('-2 years', '-1 month'),
            'next_maintenance_due' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
            'maintenance_notes' => $this->faker->optional()->sentence(),
            'purchase_cost' => $purchaseCost,
            'current_value' => $currentValue,
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'supplier' => $this->faker->optional()->company(),
            'warranty_period' => $this->faker->optional()->numberBetween(6, 36), // months
            'warranty_expires' => $this->faker->optional()->dateTimeBetween('now', '+3 years'),
            'is_active' => true,
            'is_rentable' => $this->faker->boolean(90), // 90% chance of being rentable
            'requires_cleaning' => $this->faker->boolean(10),
            'requires_inspection' => $this->faker->boolean(5),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Configure the factory for low stock items.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $this->faker->numberBetween(0, 2),
            'minimum_stock_level' => $this->faker->numberBetween(3, 5),
        ]);
    }

    /**
     * Configure the factory for out of stock items.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => 0,
            'minimum_stock_level' => $this->faker->numberBetween(1, 5),
        ]);
    }

    /**
     * Configure the factory for items needing maintenance.
     */
    public function needsMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_due' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    /**
     * Configure the factory for items with warranty expiring soon.
     */
    public function warrantyExpiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expires' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
        ]);
    }
}
