<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Customer info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            // Request details
            $table->date('date');
            $table->time('preferred_time')->nullable();
            $table->unsignedTinyInteger('guests');

            // Status
            $table->string('status')->default('waiting'); // WaitlistStatus enum

            // Timestamps
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes
            $table->index(['restaurant_id', 'date']);
            $table->index(['restaurant_id', 'status']);
            $table->index('status');
            $table->index('customer_email');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist');
    }
};
