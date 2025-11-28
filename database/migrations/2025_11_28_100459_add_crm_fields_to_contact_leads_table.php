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
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('status', 50)->default('new')->after('user_agent');
            $table->foreignId('assigned_to_user_id')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable()->after('assigned_to_user_id');
            $table->foreignId('restaurant_id')->nullable()->after('internal_notes')
                ->constrained('restaurants')->nullOnDelete();

            $table->index('status');
            $table->index('assigned_to_user_id');
            $table->index('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_user_id']);
            $table->dropForeign(['restaurant_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_to_user_id']);
            $table->dropIndex(['restaurant_id']);
            $table->dropColumn(['status', 'assigned_to_user_id', 'internal_notes', 'restaurant_id']);
        });
    }
};
