<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->char('country', 2)->default('DE');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone')->default('Europe/Berlin');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedInteger('restaurant_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('country');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
