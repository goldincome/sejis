<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique(); // Unique identifier for each inventory item
            $table->string('serial_number')->nullable(); // For equipment tracking
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0); // Reserved for orders
            $table->integer('quantity_available')->storedAs('quantity_on_hand - quantity_reserved');
            $table->integer('minimum_stock_level')->default(1); // Low stock alert threshold
            $table->integer('maximum_stock_level')->nullable(); // Maximum capacity
            
            // Location tracking
            $table->string('location')->nullable(); // Warehouse, Storage Area A, Kitchen Unit 1, etc.
            $table->string('zone')->nullable(); // Specific zone within location
            $table->string('shelf_position')->nullable(); // Exact position for small items
            
            // Condition and maintenance tracking
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'needs_repair', 'out_of_service'])
                  ->default('good');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_due')->nullable();
            $table->text('maintenance_notes')->nullable();
            
            // Purchase and valuation
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('supplier')->nullable();
            $table->string('warranty_period')->nullable(); // e.g., '2 years', '6 months'
            $table->date('warranty_expires')->nullable();
            
            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->boolean('is_rentable')->default(true);
            $table->boolean('requires_cleaning')->default(false);
            $table->boolean('requires_inspection')->default(false);
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['product_id', 'is_active']);
            $table->index(['condition', 'is_rentable']);
            $table->index(['location', 'zone']);
            $table->index(['quantity_on_hand', 'minimum_stock_level']);
            $table->index(['next_maintenance_due']);
            $table->index(['warranty_expires']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
