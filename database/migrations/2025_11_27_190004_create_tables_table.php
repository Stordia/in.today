<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('seats');
            $table->unsignedTinyInteger('min_guests')->default(1);
            $table->unsignedTinyInteger('max_guests')->nullable();
            $table->string('zone')->nullable(); // indoor, terrace, bar, etc.
            $table->boolean('is_combinable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['restaurant_id', 'zone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
