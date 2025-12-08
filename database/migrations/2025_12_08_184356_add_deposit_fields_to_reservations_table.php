<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('deposit_required')->default(false)->after('user_agent');
            $table->decimal('deposit_amount', 8, 2)->nullable()->after('deposit_required');
            $table->char('deposit_currency', 3)->nullable()->after('deposit_amount');
            $table->string('deposit_status', 20)->default('none')->after('deposit_currency');
            $table->text('deposit_notes')->nullable()->after('deposit_status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_required',
                'deposit_amount',
                'deposit_currency',
                'deposit_status',
                'deposit_notes',
            ]);
        });
    }
};
