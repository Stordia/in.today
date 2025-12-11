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
 * Tests for Admin Restaurant country/city relationship and dependent dropdowns.
 *
 * Ensures that:
 * - Cities are correctly filtered by selected country
 * - Editing a restaurant's country and city persists correctly
 * - Data consistency is maintained between country_id and city_id
 *
 * @group admin-restaurant
 */
class AdminRestaurantCountryCityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Country $germany;

    protected Country $france;

    protected City $berlin;

    protected City $paris;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Create countries
        $this->germany = Country::create([
            'code' => 'DE',
            'name' => 'Germany',
            'slug' => 'germany',
            'is_active' => true,
        ]);

        $this->france = Country::create([
            'code' => 'FR',
            'name' => 'France',
            'slug' => 'france',
            'is_active' => true,
        ]);

        // Create cities
        $this->berlin = City::create([
            'name' => 'Berlin',
            'slug' => 'berlin',
            'country_id' => $this->germany->id,
            'is_active' => true,
        ]);

        $this->paris = City::create([
            'name' => 'Paris',
            'slug' => 'paris',
            'country_id' => $this->france->id,
            'is_active' => true,
        ]);
    }

    public function test_restaurant_can_be_created_with_country_and_city(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'country_id' => $this->germany->id,
            'city_id' => $this->berlin->id,
            'timezone' => 'Europe/Berlin',
        ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'country_id' => $this->germany->id,
            'city_id' => $this->berlin->id,
        ]);

        // Verify relationships work correctly
        $restaurant->refresh();
        $this->assertEquals('Germany', $restaurant->country()->first()->name);
        $this->assertEquals('Berlin', $restaurant->city()->first()->name);
    }

    public function test_restaurant_city_belongs_to_correct_country(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Berlin Restaurant',
            'slug' => 'berlin-restaurant',
            'country_id' => $this->germany->id,
            'city_id' => $this->berlin->id,
            'timezone' => 'Europe/Berlin',
        ]);

        $restaurant->refresh();

        // The city's country should match the restaurant's country
        $city = $restaurant->city()->first();
        $this->assertNotNull($city);
        $this->assertEquals($this->germany->id, $city->country_id);
    }

    public function test_changing_restaurant_country_and_city_persists_correctly(): void
    {
        // Create restaurant in Germany/Berlin
        $restaurant = Restaurant::create([
            'name' => 'Moving Restaurant',
            'slug' => 'moving-restaurant',
            'country_id' => $this->germany->id,
            'city_id' => $this->berlin->id,
            'timezone' => 'Europe/Berlin',
        ]);

        // Change to France/Paris
        $restaurant->update([
            'country_id' => $this->france->id,
            'city_id' => $this->paris->id,
            'timezone' => 'Europe/Paris',
        ]);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'country_id' => $this->france->id,
            'city_id' => $this->paris->id,
        ]);

        $restaurant->refresh();
        $this->assertEquals('France', $restaurant->country()->first()->name);
        $this->assertEquals('Paris', $restaurant->city()->first()->name);

        // Verify city belongs to correct country
        $city = $restaurant->city()->first();
        $this->assertEquals($this->france->id, $city->country_id);
    }

    public function test_city_query_filters_by_country_correctly(): void
    {
        // Query cities for Germany
        $germanCities = City::query()
            ->where('country_id', $this->germany->id)
            ->pluck('name', 'id');

        $this->assertCount(1, $germanCities);
        $this->assertTrue($germanCities->contains('Berlin'));
        $this->assertFalse($germanCities->contains('Paris'));

        // Query cities for France
        $frenchCities = City::query()
            ->where('country_id', $this->france->id)
            ->pluck('name', 'id');

        $this->assertCount(1, $frenchCities);
        $this->assertTrue($frenchCities->contains('Paris'));
        $this->assertFalse($frenchCities->contains('Berlin'));
    }

    public function test_multiple_cities_can_belong_to_same_country(): void
    {
        // Add another German city
        $munich = City::create([
            'name' => 'Munich',
            'slug' => 'munich',
            'country_id' => $this->germany->id,
            'is_active' => true,
        ]);

        $germanCities = City::query()
            ->where('country_id', $this->germany->id)
            ->orderBy('name')
            ->pluck('name', 'id');

        $this->assertCount(2, $germanCities);
        $this->assertTrue($germanCities->contains('Berlin'));
        $this->assertTrue($germanCities->contains('Munich'));
    }

    public function test_admin_can_view_restaurant_edit_form(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'country_id' => $this->germany->id,
            'city_id' => $this->berlin->id,
            'timezone' => 'Europe/Berlin',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/restaurants/{$restaurant->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
        $response->assertSee('Country');
        $response->assertSee('City');
    }
}
