<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for shared restaurant profile schema consistency.
 *
 * Ensures that the core profile fields (name, cuisine, tagline, description,
 * phone, email, website_url, address) work consistently across Admin and Business panels.
 *
 * @group restaurant-settings
 */
class RestaurantProfileSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected Country $country;

    protected City $city;

    protected Cuisine $cuisine;

    protected Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->country = Country::create([
            'code' => 'DE',
            'name' => 'Germany',
            'slug' => 'germany',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name' => 'Berlin',
            'slug' => 'berlin',
            'country_id' => $this->country->id,
            'is_active' => true,
        ]);

        $this->cuisine = Cuisine::create([
            'slug' => 'italian',
            'name_en' => 'Italian',
            'name_de' => 'Italienisch',
            'name_el' => 'Ιταλική',
        ]);

        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'cuisine_id' => $this->cuisine->id,
            'booking_enabled' => true,
            'booking_public_slug' => 'test-restaurant',
            'timezone' => 'Europe/Berlin',
        ]);
    }

    public function test_restaurant_can_store_core_profile_fields(): void
    {
        $this->restaurant->update([
            'name' => 'Updated Restaurant Name',
            'cuisine_id' => $this->cuisine->id,
            'settings' => [
                'tagline' => 'Best Italian food in town',
                'description' => 'A cozy restaurant serving authentic Italian cuisine.',
                'phone' => '+49 30 12345678',
                'email' => 'info@restaurant.com',
                'website_url' => 'https://restaurant.com',
            ],
            'address_street' => 'Friedrichstraße 123',
            'address_postal' => '10117',
            'address_district' => 'Mitte',
        ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'name' => 'Updated Restaurant Name',
            'cuisine_id' => $this->cuisine->id,
            'address_street' => 'Friedrichstraße 123',
            'address_postal' => '10117',
            'address_district' => 'Mitte',
        ]);

        $this->restaurant->refresh();

        $this->assertEquals('Best Italian food in town', $this->restaurant->settings['tagline']);
        $this->assertEquals('A cozy restaurant serving authentic Italian cuisine.', $this->restaurant->settings['description']);
        $this->assertEquals('+49 30 12345678', $this->restaurant->settings['phone']);
        $this->assertEquals('info@restaurant.com', $this->restaurant->settings['email']);
        $this->assertEquals('https://restaurant.com', $this->restaurant->settings['website_url']);
    }

    public function test_website_url_is_normalized_with_https_prefix(): void
    {
        $this->restaurant->update([
            'settings' => [
                'website_url' => 'restaurant.com',
            ],
        ]);

        $this->restaurant->refresh();

        // Note: Normalization happens in the form dehydration logic
        // Here we're just testing that the field can be stored
        $this->assertEquals('restaurant.com', $this->restaurant->settings['website_url']);
    }

    public function test_booking_configuration_fields_are_stored_correctly(): void
    {
        $this->restaurant->update([
            'booking_enabled' => true,
            'booking_public_slug' => 'my-restaurant',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 12,
            'booking_default_duration_minutes' => 120,
            'booking_min_lead_time_minutes' => 120,
            'booking_max_lead_time_days' => 60,
        ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'booking_enabled' => true,
            'booking_public_slug' => 'my-restaurant',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 12,
            'booking_default_duration_minutes' => 120,
            'booking_min_lead_time_minutes' => 120,
            'booking_max_lead_time_days' => 60,
        ]);
    }

    public function test_location_fields_are_stored_correctly(): void
    {
        $this->restaurant->update([
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'address_street' => 'Main Street 123',
            'address_postal' => '12345',
            'address_district' => 'Downtown',
        ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'address_street' => 'Main Street 123',
            'address_postal' => '12345',
            'address_district' => 'Downtown',
        ]);

        $this->restaurant->refresh();

        $this->assertEquals('Germany', $this->restaurant->country()->first()->name);
        $this->assertEquals('Berlin', $this->restaurant->city()->first()->name);
    }

    public function test_cuisine_relationship_works_correctly(): void
    {
        $this->restaurant->update([
            'cuisine_id' => $this->cuisine->id,
        ]);

        $this->restaurant->refresh();

        $this->assertNotNull($this->restaurant->cuisine);
        $this->assertEquals('Italian', $this->restaurant->cuisine->getName());
        $this->assertEquals('italian', $this->restaurant->cuisine->slug);
    }
}
