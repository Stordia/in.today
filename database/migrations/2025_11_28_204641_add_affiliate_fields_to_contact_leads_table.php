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
            $table->foreignId('affiliate_id')
                ->nullable()
                ->after('restaurant_id')
                ->constrained('affiliates')
                ->nullOnDelete();

            $table->foreignId('affiliate_link_id')
                ->nullable()
                ->after('affiliate_id')
                ->constrained('affiliate_links')
                ->nullOnDelete();

            $table->index('affiliate_id');
            $table->index('affiliate_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropForeign(['affiliate_link_id']);
            $table->dropIndex(['affiliate_id']);
            $table->dropIndex(['affiliate_link_id']);
            $table->dropColumn(['affiliate_id', 'affiliate_link_id']);
        });
    }
};
