<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('uuid')->unique();

            // Customer info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            // Reservation details
            $table->date('date');
            $table->time('time');
            $table->unsignedTinyInteger('guests');
            $table->unsignedSmallInteger('duration_minutes')->default(120);

            // Table assignment
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();

            // Status and source
            $table->string('status')->default('pending'); // ReservationStatus enum
            $table->string('source')->default('platform'); // ReservationSource enum

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Metadata
            $table->string('language', 5)->default('en');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Status timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['restaurant_id', 'date']);
            $table->index(['restaurant_id', 'status']);
            $table->index('status');
            $table->index('customer_email');
            $table->index('user_id');
            $table->index('table_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
