<?php

use App\Enums\OrderStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('order_no')->unique();
            $table->decimal('total', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->string('payment_method');
            $table->string('payment_method_order_id')->nullable();
            $table->string('currency')->default('GBP');
            $table->string('stripe_payment_intent')->nullable();
            $table->string('status')->default(OrderStatusEnum::PENDING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
