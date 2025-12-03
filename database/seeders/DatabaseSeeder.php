<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed platform settings (email, booking, affiliate, technical settings)
        $this->call(AppSettingsSeeder::class);

        // Seed demo countries and cities for development environments
        // For full geo data, upload worldcities.csv and run: php artisan world:import-cities
        $this->call(WorldDirectoryDemoSeeder::class);
    }
}
