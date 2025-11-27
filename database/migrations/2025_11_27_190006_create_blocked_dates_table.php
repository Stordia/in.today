<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_all_day')->default(true);
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'date']);
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
    }
};
