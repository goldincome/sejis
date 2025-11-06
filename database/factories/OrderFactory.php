<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 50, 1000);
        $tax = $subTotal * 0.2; // 20% tax
        $total = $subTotal + $tax;
        
        return [
            'user_id' => User::factory(),
            'order_no' => 'ORD-' . $this->faker->unique()->numberBetween(10000, 99999),
            'reference' => 'REF-' . Str::random(8),
            'total' => $total,
            'sub_total' => $subTotal,
            'tax' => $tax,
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal', 'take_payments', 'bank_deposit']),
            'payment_method_order_id' => $this->faker->optional()->uuid(),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal', 'take_payments']),
            'transaction_id' => $this->faker->optional()->uuid(),
            'currency' => 'GBP',
            'status' => $this->faker->randomElement(OrderStatusEnum::cases()),
            'failure_reason' => null,
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::PENDING,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::CONFIRMED,
            'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the order is active (rental in progress).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::ACTIVE,
            'paid_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::COMPLETED,
            'paid_at' => $this->faker->dateTimeBetween('-2 months', '-1 week'),
        ]);
    }
}
