<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove redundant name and email columns from restaurant_users pivot table.
 *
 * These columns were originally denormalized copies of user data, but since
 * we have a proper user_id foreign key, they are unnecessary and cause issues
 * with Filament's AttachAction which doesn't populate them.
 *
 * The user's name and email can be accessed via the users relationship.
 */
return new class extends Migration
{
    public function up(): void
    {
        // This migration is no longer needed since the original create table migration
        // was updated to not include name and email columns in the first place.
        // We keep this migration file for historical reasons and for existing databases
        // that already have the name and email columns.

        // SQLite doesn't support dropping columns in the same way MySQL does.
        // For test environments using SQLite, we skip this migration.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Check if the columns exist before trying to drop them
        $hasNameColumn = Schema::hasColumn('restaurant_users', 'name');
        $hasEmailColumn = Schema::hasColumn('restaurant_users', 'email');

        if (! $hasNameColumn && ! $hasEmailColumn) {
            // Columns don't exist, nothing to do
            return;
        }

        if ($hasEmailColumn) {
            Schema::table('restaurant_users', function (Blueprint $table) {
                // Drop the unique constraint that includes email first
                $table->dropUnique(['restaurant_id', 'email']);
            });
        }

        Schema::table('restaurant_users', function (Blueprint $table) use ($hasNameColumn, $hasEmailColumn) {
            // Now drop the columns that exist
            $columnsToDrop = [];
            if ($hasNameColumn) {
                $columnsToDrop[] = 'name';
            }
            if ($hasEmailColumn) {
                $columnsToDrop[] = 'email';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Add a new unique constraint on restaurant_id + user_id if it doesn't exist
        if (! $this->hasUniqueIndex('restaurant_users', ['restaurant_id', 'user_id'])) {
            Schema::table('restaurant_users', function (Blueprint $table) {
                $table->unique(['restaurant_id', 'user_id']);
            });
        }
    }

    /**
     * Check if a unique index exists on the given columns.
     */
    private function hasUniqueIndex(string $table, array $columns): bool
    {
        $connection = Schema::getConnection();
        $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($table);

        foreach ($indexes as $index) {
            if ($index->isUnique() && $index->getColumns() === $columns) {
                return true;
            }
        }

        return false;
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('restaurant_users', function (Blueprint $table) {
            $table->dropUnique(['restaurant_id', 'user_id']);
        });

        Schema::table('restaurant_users', function (Blueprint $table) {
            $table->string('name')->after('user_id');
            $table->string('email')->after('name');
        });

        Schema::table('restaurant_users', function (Blueprint $table) {
            $table->unique(['restaurant_id', 'email']);
        });
    }
};
