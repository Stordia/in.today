<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->nullOnDelete();
            $table->string('timezone')->default('Europe/Berlin');
            $table->char('country', 2)->default('DE');
            $table->json('settings')->nullable();
            $table->string('plan')->default('starter'); // starter, pro, business
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('agency_id');
            $table->index('is_active');
            $table->index(['is_active', 'is_verified']);
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
