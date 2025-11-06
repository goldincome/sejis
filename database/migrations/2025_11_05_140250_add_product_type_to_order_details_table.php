<?php

use App\Enums\ProductTypeEnum;
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
        Schema::table('order_details', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->after('booked_durations');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->string('product_type')->default(ProductTypeEnum::KITCHEN_RENTAL->value)->after('booked_durations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('product_type');
        });
    }
};
