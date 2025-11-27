<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // City relationship
            $table->foreignId('city_id')->nullable()->after('agency_id')
                ->constrained('cities')->nullOnDelete();

            // Address fields
            $table->string('address_street')->nullable()->after('country');
            $table->string('address_district')->nullable()->after('address_street');
            $table->string('address_postal')->nullable()->after('address_district');
            $table->char('address_country', 2)->default('DE')->after('address_postal');

            // Geo coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('address_country');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // Classification
            $table->foreignId('cuisine_id')->nullable()->after('longitude')
                ->constrained('cuisines')->nullOnDelete();
            $table->tinyInteger('price_range')->nullable()->after('cuisine_id'); // 1-4

            // Stats (cached counters)
            $table->decimal('avg_rating', 2, 1)->default(0.0)->after('price_range');
            $table->unsignedInteger('review_count')->default(0)->after('avg_rating');
            $table->unsignedInteger('reservation_count')->default(0)->after('review_count');

            // Features & media
            $table->json('features')->nullable()->after('reservation_count'); // ["outdoor_seating", "wifi"]
            $table->string('logo_url')->nullable()->after('features');
            $table->string('cover_image_url')->nullable()->after('logo_url');

            // Indexes
            $table->index('city_id');
            $table->index('cuisine_id');
            $table->index('price_range');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['cuisine_id']);
            $table->dropIndex(['city_id']);
            $table->dropIndex(['cuisine_id']);
            $table->dropIndex(['price_range']);
            $table->dropIndex(['latitude', 'longitude']);

            $table->dropColumn([
                'city_id',
                'address_street',
                'address_district',
                'address_postal',
                'address_country',
                'latitude',
                'longitude',
                'cuisine_id',
                'price_range',
                'avg_rating',
                'review_count',
                'reservation_count',
                'features',
                'logo_url',
                'cover_image_url',
            ]);
        });
    }
};
