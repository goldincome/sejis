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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json, file
            $table->string('group')->default('general'); // general, payment, email, site
            $table->boolean('is_public')->default(false); // Can be accessed in frontend
            $table->boolean('is_encrypted')->default(false); // For sensitive data
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['group', 'key']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
