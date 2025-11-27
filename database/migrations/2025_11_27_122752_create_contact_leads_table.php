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
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->id();

            $table->string('locale', 5)->default('en');

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('restaurant_name')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('website_url')->nullable();

            $table->string('type')->nullable();
            $table->json('services')->nullable();
            $table->string('budget')->nullable();

            $table->text('message')->nullable();

            $table->string('source_url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('locale');
            $table->index('email');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
