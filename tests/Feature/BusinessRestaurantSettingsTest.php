<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RestaurantRole;
use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use App\Support\Tenancy\CurrentRestaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Business Panel unified Restaurant Settings page.
 *
 * Verifies that:
 * 1. Restaurant users can access the settings page
 * 2. Profile, Booking, and Deposit settings can be updated
 * 3. Public booking page reflects the saved settings
 */
class BusinessRestaurantSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private User $restaurantOwner;

    private Country $country;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::create([
            'name' => 'Germany',
            'code' => 'DE',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name' => 'Berlin',
            'country_id' => $this->country->id,
            'is_active' => true,
        ]);

        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_public_slug' => 'test-restaurant',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 10,
            'booking_default_duration_minutes' => 90,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
        ]);

        $this->restaurantOwner = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
        ]);

        RestaurantUser::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->restaurantOwner->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Access Tests
    |--------------------------------------------------------------------------
    */

    public function test_restaurant_user_can_access_settings_page(): void
    {
        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/restaurant-settings');

        $response->assertStatus(200);
    }

    public function test_settings_page_shows_restaurant_name(): void
    {
        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/restaurant-settings');

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->get('/business/restaurant-settings');

        $response->assertRedirect();
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Settings Tests
    |--------------------------------------------------------------------------
    */

    public function test_profile_settings_are_loaded_correctly(): void
    {
        $this->restaurant->update([
            'name' => 'Test Restaurant Updated',
            'settings' => [
                'tagline' => 'Best food in town',
                'phone' => '+49 123 456789',
            ],
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/restaurant-settings');

        $response->assertStatus(200);
        $response->assertSee('Best food in town');
    }

    /*
    |--------------------------------------------------------------------------
    | Booking Settings Tests
    |--------------------------------------------------------------------------
    */

    public function test_booking_settings_are_loaded_correctly(): void
    {
        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/restaurant-settings');

        $response->assertStatus(200);
        // Check that the booking slug is shown
        $response->assertSee('test-restaurant');
    }

    /*
    |--------------------------------------------------------------------------
    | Deposit Settings Tests
    |--------------------------------------------------------------------------
    */

    public function test_deposit_settings_affect_public_booking_page(): void
    {
        // Enable deposits for the restaurant
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 4,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 15.00,
            'booking_deposit_currency' => 'EUR',
        ]);

        // Test that the public booking page shows deposit info for a large party
        $response = $this->get('/book/test-restaurant?party_size=6');

        $response->assertStatus(200);
        // Should show deposit info for party of 6 (above threshold of 4)
        $response->assertSee('90'); // 6 guests × €15 = €90
    }

    public function test_deposit_not_required_when_disabled(): void
    {
        // Disable deposits
        $this->restaurant->update([
            'booking_deposit_enabled' => false,
        ]);

        // Test that the requiresDeposit method returns false
        $this->assertFalse($this->restaurant->requiresDeposit(6));
        $this->assertFalse($this->restaurant->requiresDeposit(10));

        // The public booking page should load without errors
        $response = $this->get('/book/test-restaurant?party_size=6');
        $response->assertStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Booking Page Reflects Settings Tests
    |--------------------------------------------------------------------------
    */

    public function test_public_booking_page_respects_party_size_limits(): void
    {
        $this->restaurant->update([
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 8,
        ]);

        $response = $this->get('/book/test-restaurant');

        $response->assertStatus(200);
        // Should show party size options from 2 to 8
        $response->assertSee('2–8');
    }

    public function test_public_booking_page_shows_booking_info(): void
    {
        $this->restaurant->update([
            'booking_max_lead_time_days' => 14,
        ]);

        $response = $this->get('/book/test-restaurant');

        $response->assertStatus(200);
        // Should show max lead time info
        $response->assertSee('14');
    }
}
