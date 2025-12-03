<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, but the schema is already
            // compatible since SQLite stores strings dynamically
            return;
        }

        DB::statement('ALTER TABLE cities MODIFY country VARCHAR(191) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * Note: Reverting will truncate any country names longer than 2 characters.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE cities MODIFY country CHAR(2) NOT NULL DEFAULT 'DE'");
    }
};
