<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds Greece with canonical code 'gr'.
     * Also updates any existing 'el' country codes to 'gr' (idempotent).
     */
    public function up(): void
    {
        // Update any existing 'EL' or 'el' country codes to 'GR'
        DB::table('countries')
            ->whereRaw('LOWER(code) = ?', ['el'])
            ->update(['code' => 'GR']);

        // Insert Greece if it doesn't exist
        $exists = DB::table('countries')
            ->whereRaw('LOWER(code) = ?', ['gr'])
            ->exists();

        if (! $exists) {
            DB::table('countries')->insert([
                'code' => 'GR',
                'name' => 'Greece',
                'slug' => 'greece',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Greece country
        DB::table('countries')
            ->whereRaw('LOWER(code) = ?', ['gr'])
            ->delete();
    }
};
