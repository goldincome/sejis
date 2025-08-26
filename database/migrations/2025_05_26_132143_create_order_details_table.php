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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ref_no')->unique();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->string('status')->default(OrderStatusEnum::PENDING->value);
            $table->dateTime('booked_date')->nullable();
            $table->json('booked_durations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};

