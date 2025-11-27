<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Monday, 6=Sunday
            $table->boolean('is_open')->default(true);
            $table->string('shift_name')->nullable(); // Lunch, Dinner, etc.
            $table->time('open_time');
            $table->time('close_time');
            $table->time('last_reservation_time')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'day_of_week']);
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
