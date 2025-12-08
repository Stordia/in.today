<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GlobalRole;
use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test to ensure the Bookings section is visible on RestaurantResource edit form.
 */
class RestaurantBookingsUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_edit_form_shows_bookings_section(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Create required related models
        $country = Country::create([
            'name' => 'Germany',
            'code' => 'DE',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Berlin',
            'country_id' => $country->id,
            'is_active' => true,
        ]);

        // Create a restaurant
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'city_id' => $city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 8,
        ]);

        // Visit the edit page as admin
        $response = $this->actingAs($admin)
            ->get("/admin/restaurants/{$restaurant->id}/edit");

        $response->assertStatus(200);

        // Assert the Bookings section is present
        $response->assertSee('Bookings');
        $response->assertSee('Enable online bookings');
        $response->assertSee('booking_enabled');
        $response->assertSee('Minimum Party Size');
        $response->assertSee('Maximum Party Size');
    }

    public function test_restaurant_create_form_shows_bookings_section(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Visit the create page as admin
        $response = $this->actingAs($admin)
            ->get('/admin/restaurants/create');

        $response->assertStatus(200);

        // Assert the Bookings section is present
        $response->assertSee('Bookings');
        $response->assertSee('Enable online bookings');
        $response->assertSee('booking_enabled');
    }
}
