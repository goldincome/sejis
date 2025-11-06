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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who made the change
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null'); // Related order if applicable
            
            // Movement details
            $table->enum('movement_type', [
                'stock_in',           // New stock received
                'stock_out',          // Stock removed/sold
                'rental_out',         // Item rented out
                'rental_return',      // Item returned from rental
                'transfer',           // Location transfer
                'adjustment',         // Stock count adjustment
                'damage',             // Item damaged
                'repair',             // Item sent for repair
                'repair_return',      // Item returned from repair
                'maintenance',        // Maintenance performed
                'disposal',           // Item disposed/scrapped
                'reservation',        // Stock reserved for order
                'unreservation'       // Stock reservation released
            ]);
            
            $table->integer('quantity_change'); // Can be positive or negative
            $table->integer('quantity_before'); // Stock level before change
            $table->integer('quantity_after'); // Stock level after change
            
            // Location tracking
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            
            // Reference information
            $table->string('reference_number')->nullable(); // PO number, repair ticket, etc.
            $table->string('supplier')->nullable(); // For stock_in movements
            $table->decimal('cost_per_unit', 10, 2)->nullable(); // For valuation tracking
            $table->decimal('total_cost', 10, 2)->nullable();
            
            // Additional details
            $table->text('reason')->nullable(); // Reason for the movement
            $table->text('notes')->nullable(); // Additional notes
            $table->json('metadata')->nullable(); // Extra data (condition changes, etc.)
            
            // Timestamps
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();
            
            // Indexes for performance and reporting
            $table->index(['inventory_item_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
            $table->index(['user_id', 'movement_date']);
            $table->index(['order_id']);
            $table->index(['from_location', 'to_location']);
            $table->index(['supplier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
