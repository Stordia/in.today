<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add 'profile' column to blocked_dates table for future-proof hour types.
 *
 * This allows blocking dates for specific profiles:
 * - 'booking' (default) - blocks online reservations
 * - 'kitchen' - blocks kitchen hours (future)
 * - 'club' - blocks club/event hours (future)
 * - 'delivery' - blocks delivery service (future)
 *
 * For now, all records default to 'booking' profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->string('profile', 50)->default('booking')->after('restaurant_id');
            $table->index(['restaurant_id', 'profile', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'profile', 'date']);
            $table->dropColumn('profile');
        });
    }
};
