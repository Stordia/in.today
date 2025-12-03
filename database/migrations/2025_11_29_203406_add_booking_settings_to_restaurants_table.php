<?php

declare(strict_types=1);

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
        Schema::table('restaurants', function (Blueprint $table) {
            // Booking settings
            $table->boolean('booking_enabled')->default(false)->after('is_featured');
            $table->string('booking_public_slug')->nullable()->unique()->after('booking_enabled');
            $table->unsignedTinyInteger('booking_min_party_size')->default(1)->after('booking_public_slug');
            $table->unsignedTinyInteger('booking_max_party_size')->default(20)->after('booking_min_party_size');
            $table->unsignedSmallInteger('booking_default_duration_minutes')->default(90)->after('booking_max_party_size');
            $table->unsignedSmallInteger('booking_min_lead_time_minutes')->default(60)->after('booking_default_duration_minutes');
            $table->unsignedSmallInteger('booking_max_lead_time_days')->default(30)->after('booking_min_lead_time_minutes');
            $table->text('booking_notes_internal')->nullable()->after('booking_max_lead_time_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'booking_enabled',
                'booking_public_slug',
                'booking_min_party_size',
                'booking_max_party_size',
                'booking_default_duration_minutes',
                'booking_min_lead_time_minutes',
                'booking_max_lead_time_days',
                'booking_notes_internal',
            ]);
        });
    }
};
