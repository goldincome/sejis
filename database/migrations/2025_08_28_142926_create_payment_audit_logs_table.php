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
        Schema::create('payment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('gateway'); // stripe, paypal, takepayments, bank_deposit
            $table->string('transaction_id')->nullable();
            $table->string('event_type'); // payment_attempt, payment_success, payment_failed, refund, chargeback, etc.
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->string('status'); // pending, completed, failed, cancelled, etc.
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('webhook_id')->nullable();
            $table->timestamps();
            
            $table->index(['gateway', 'status']);
            $table->index(['event_type', 'created_at']);
            $table->index(['order_id', 'gateway']);
            $table->index(['user_id', 'created_at']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_audit_logs');
    }
};
