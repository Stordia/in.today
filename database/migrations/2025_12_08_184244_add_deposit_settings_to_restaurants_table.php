<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('booking_deposit_enabled')->default(false)->after('booking_notes_internal');
            $table->unsignedTinyInteger('booking_deposit_threshold_party_size')->default(4)->after('booking_deposit_enabled');
            $table->string('booking_deposit_type', 30)->default('fixed_per_person')->after('booking_deposit_threshold_party_size');
            $table->decimal('booking_deposit_amount', 8, 2)->default(0.00)->after('booking_deposit_type');
            $table->char('booking_deposit_currency', 3)->default('EUR')->after('booking_deposit_amount');
            $table->text('booking_deposit_policy')->nullable()->after('booking_deposit_currency');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'booking_deposit_enabled',
                'booking_deposit_threshold_party_size',
                'booking_deposit_type',
                'booking_deposit_amount',
                'booking_deposit_currency',
                'booking_deposit_policy',
            ]);
        });
    }
};
