<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add order_amount column to affiliate_conversions table.
 * This represents the base order value from which commission is calculated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->decimal('order_amount', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->dropColumn('order_amount');
        });
    }
};
