<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_lead_emails', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_lead_id')
                ->constrained('contact_leads')
                ->cascadeOnDelete();

            $table->foreignId('sent_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('to_email');
            $table->string('subject');
            $table->longText('body');
            $table->string('status')->default('sent');
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('contact_lead_id');
            $table->index('sent_by_user_id');
            $table->index('to_email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_lead_emails');
    }
};
