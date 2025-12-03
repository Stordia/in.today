<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes cities.country from CHAR(2) to VARCHAR(191) to store full country names.
     * The column was originally designed for ISO country codes (e.g., "DE"),
     * but is now used to store full country names (e.g., "Germany").
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE cities MODIFY country VARCHAR(191) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * Note: Reverting will truncate any country names longer than 2 characters.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE cities MODIFY country CHAR(2) NOT NULL DEFAULT 'DE'");
    }
};
