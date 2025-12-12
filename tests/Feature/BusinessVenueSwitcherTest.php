<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GlobalRole;
use App\Enums\RestaurantRole;
use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use App\Support\Tenancy\CurrentRestaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessVenueSwitcherTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Restaurant $restaurant1;

    private Restaurant $restaurant2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary location data
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

        // Create user
        $this->user = User::create([
            'name' => 'Multi-Venue Owner',
            'email' => 'owner@multi.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::User,
        ]);

        // Create two restaurants
        $this->restaurant1 = Restaurant::create([
            'name' => 'First Restaurant',
            'city_id' => $city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
        ]);

        $this->restaurant2 = Restaurant::create([
            'name' => 'Second Restaurant',
            'city_id' => $city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
        ]);

        // Link user to both restaurants
        RestaurantUser::create([
            'restaurant_id' => $this->restaurant1->id,
            'user_id' => $this->user->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);

        RestaurantUser::create([
            'restaurant_id' => $this->restaurant2->id,
            'user_id' => $this->user->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);
    }

    public function test_business_user_sees_venue_switcher_when_multiple_restaurants(): void
    {
        $this->actingAs($this->user);

        // Visit any business panel page
        $response = $this->get('/business');

        $response->assertStatus(200);

        // Check that both restaurant names appear in the switcher
        $response->assertSee('First Restaurant');
        $response->assertSee('Second Restaurant');
    }

    public function test_venue_switcher_updates_current_restaurant_context(): void
    {
        $this->actingAs($this->user);

        // Initially, first restaurant should be current (alphabetically first)
        $initial = CurrentRestaurant::get();
        $this->assertEquals('First Restaurant', $initial->name);

        // Switch to second restaurant using query parameter
        $response = $this->get("/business/switch-restaurant?restaurant_id={$this->restaurant2->id}");

        // Should redirect to dashboard
        $response->assertRedirect('/business');

        // Follow redirect
        $response = $this->get('/business');
        $response->assertStatus(200);

        // Current restaurant should now be the second one
        $current = CurrentRestaurant::get();
        $this->assertNotNull($current);
        $this->assertEquals($this->restaurant2->id, $current->id);
        $this->assertEquals('Second Restaurant', $current->name);
    }

    public function test_no_switcher_when_single_restaurant(): void
    {
        // Create a new user with only one restaurant
        $singleUser = User::create([
            'name' => 'Single Venue Owner',
            'email' => 'owner@single.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::User,
        ]);

        RestaurantUser::create([
            'restaurant_id' => $this->restaurant1->id,
            'user_id' => $singleUser->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);

        $this->actingAs($singleUser);

        $response = $this->get('/business');

        $response->assertStatus(200);

        // Should show restaurant name but without dropdown/caret
        $response->assertSee('First Restaurant');

        // Should not show the second restaurant
        $response->assertDontSee('Second Restaurant');
    }

    public function test_opening_hours_page_reflects_switched_restaurant(): void
    {
        $this->actingAs($this->user);

        // Create opening hours for restaurant2
        \App\Models\OpeningHour::create([
            'restaurant_id' => $this->restaurant2->id,
            'profile' => 'booking',
            'day_of_week' => 0, // Monday
            'is_open' => true,
            'open_time' => '17:00',
            'close_time' => '23:00',
        ]);

        // Switch to restaurant2
        CurrentRestaurant::set($this->restaurant2->id);

        // Visit opening hours page
        $response = $this->get('/business/opening-hours');

        $response->assertStatus(200);

        // Should see opening hours for restaurant2
        $response->assertSee('17:00');
        $response->assertSee('23:00');

        // Subheading should NOT contain "You are managing" text anymore
        $response->assertDontSee('You are managing: Second Restaurant');

        // But should still see the generic description
        $response->assertSee('Manage when guests can book online');
    }

    public function test_unauthorized_restaurant_switch_is_blocked(): void
    {
        // Create another restaurant not linked to our user
        $country = Country::first();
        $city = City::first();

        $unauthorizedRestaurant = Restaurant::create([
            'name' => 'Unauthorized Restaurant',
            'city_id' => $city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
        ]);

        $this->actingAs($this->user);

        // Try to switch to unauthorized restaurant
        $response = $this->get("/business/switch-restaurant?restaurant_id={$unauthorizedRestaurant->id}");

        // Should not switch
        $current = CurrentRestaurant::get();
        $this->assertNotEquals($unauthorizedRestaurant->id, $current->id);
    }

    public function test_switch_via_form_submission_still_works(): void
    {
        $this->actingAs($this->user);

        // Visit the switch page
        $response = $this->get('/business/switch-restaurant');
        $response->assertStatus(200);

        // The page should show a form with both restaurants
        $response->assertSee('First Restaurant');
        $response->assertSee('Second Restaurant');
    }
}
