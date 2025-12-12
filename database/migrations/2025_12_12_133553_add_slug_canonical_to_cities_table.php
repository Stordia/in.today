<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds slug_canonical column to cities table for efficient canonical slug routing.
     * This avoids runtime Str::slug(name) calls and enables fast indexed lookups.
     *
     * After running this migration, backfill existing cities:
     * php artisan cities:backfill-canonical-slugs
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('slug_canonical')->nullable()->after('slug');
            $table->index('slug_canonical');
            $table->index(['country_id', 'slug_canonical']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['country_id', 'slug_canonical']);
            $table->dropIndex(['slug_canonical']);
            $table->dropColumn('slug_canonical');
        });
    }
};
