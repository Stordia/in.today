<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add 'profile' column to opening_hours table for future-proof hour types.
 *
 * This allows a restaurant to have different hour profiles:
 * - 'booking' (default) - hours when guests can make online reservations
 * - 'kitchen' - hours when the kitchen is open (future)
 * - 'club' - hours for club/event operations (future)
 * - 'delivery' - hours for delivery service (future)
 *
 * For now, all records default to 'booking' profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opening_hours', function (Blueprint $table) {
            $table->string('profile', 50)->default('booking')->after('restaurant_id');
            $table->index(['restaurant_id', 'profile', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::table('opening_hours', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'profile', 'day_of_week']);
            $table->dropColumn('profile');
        });
    }
};
