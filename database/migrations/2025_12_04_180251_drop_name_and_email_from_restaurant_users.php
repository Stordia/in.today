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
        // SQLite doesn't support dropping columns in the same way MySQL does.
        // For test environments using SQLite, we skip this migration.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('restaurant_users', function (Blueprint $table) {
            // Drop the unique constraint that includes email first
            $table->dropUnique(['restaurant_id', 'email']);
        });

        Schema::table('restaurant_users', function (Blueprint $table) {
            // Now drop the columns
            $table->dropColumn(['name', 'email']);
        });

        // Add a new unique constraint on restaurant_id + user_id
        Schema::table('restaurant_users', function (Blueprint $table) {
            $table->unique(['restaurant_id', 'user_id']);
        });
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
