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
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->foreignId('affiliate_payout_id')
                ->nullable()
                ->after('contact_lead_id')
                ->constrained('affiliate_payouts')
                ->nullOnDelete();

            $table->index('affiliate_payout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->dropForeign(['affiliate_payout_id']);
            $table->dropColumn('affiliate_payout_id');
        });
    }
};
