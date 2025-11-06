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
        Schema::table('products', function (Blueprint $table) {
            // Add the type column first
            $table->string('type')->default('kitchen_rental')->after('category_id');
            $table->integer('quantity')->default(1)->after('price_per_day');
            
            // Additional product information for better search
            $table->json('features')->nullable()->after('description');
            $table->json('specifications')->nullable()->after('features');
            $table->integer('capacity')->nullable()->after('specifications'); // For kitchen capacity
            $table->string('dimensions')->nullable()->after('capacity'); // e.g., "20ft x 15ft"
            $table->decimal('weight', 8, 2)->nullable()->after('dimensions'); // For equipment weight
            $table->string('brand')->nullable()->after('weight');
            $table->string('model_number')->nullable()->after('brand');
            $table->year('year_manufactured')->nullable()->after('model_number');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->text('tags')->nullable()->after('is_featured'); // Comma-separated tags
            
            // Search optimization indexes
            $table->index(['is_active', 'type']);
            $table->index(['category_id', 'is_active']);
            $table->index(['price', 'is_active']);
            $table->index(['capacity']);
            $table->index(['brand']);
            $table->index(['is_featured', 'is_active']);
            $table->fullText(['name', 'description', 'intro']); // Full-text search index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'type']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['price', 'is_active']);
            $table->dropIndex(['capacity']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['is_featured', 'is_active']);
            $table->dropFullText(['name', 'description', 'intro']);
            
            $table->dropColumn([
                'type', 'quantity', 'features', 'specifications', 'capacity', 
                'dimensions', 'weight', 'brand', 'model_number', 
                'year_manufactured', 'is_featured', 'tags'
            ]);
        });
    }
};
