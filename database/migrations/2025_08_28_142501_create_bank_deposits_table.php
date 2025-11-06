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
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('bank_reference')->unique();
            $table->decimal('deposit_amount', 10, 2);
            $table->date('deposit_date');
            $table->string('verified_by')->nullable();
            $table->text('verification_notes')->nullable();
            $table->enum('status', ['pending_verification', 'confirmed', 'rejected'])->default('pending_verification');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('deposit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
