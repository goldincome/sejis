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
        Schema::table('settings', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('settings', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('settings', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('settings', 'values')) {
                $table->dropColumn('values');
            }
            
            // Add new columns
            /*$table->string('key')->unique()->after('id');
            $table->longText('value')->nullable()->after('key');
            $table->string('type')->default('string')->after('value');
            $table->string('group')->default('general')->after('type');
            $table->boolean('is_public')->default(false)->after('group');
            $table->boolean('is_encrypted')->default(false)->after('is_public');
            $table->text('description')->nullable()->after('is_encrypted');
            */
            // Add indexes
           // $table->index(['group', 'key']);
            //$table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Drop new columns
            $table->dropIndex(['settings_group_key_index']);
            $table->dropIndex(['settings_is_public_index']);
            $table->dropColumn(['key', 'value', 'type', 'group', 'is_public', 'is_encrypted', 'description']);
            
            // Restore old columns
            $table->string('name')->after('id');
            $table->string('slug')->after('name');
            $table->json('values')->nullable()->after('slug');
        });
    }
};
