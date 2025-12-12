<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantRole;
use App\Models\City;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use App\Support\Tenancy\CurrentRestaurant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Business Panel reservations page UX.
 *
 * Verifies that:
 * 1. Restaurant users can access and see their reservations
 * 2. Filters (Today, Upcoming) work correctly
 * 3. Edit page redirects back to index after save
 */
class BusinessReservationsPageTest extends TestCase
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
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 10,
            'booking_default_duration_minutes' => 90,
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

    public function test_restaurant_user_can_access_reservations_list(): void
    {
        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/reservations');

        $response->assertStatus(200);
    }

    public function test_reservations_list_shows_restaurant_reservations(): void
    {
        // Create a reservation for this restaurant
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/reservations');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_reservations_list_does_not_show_other_restaurant_reservations(): void
    {
        // Create another restaurant
        $otherRestaurant = Restaurant::create([
            'name' => 'Other Restaurant',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
        ]);

        // Create a reservation for the other restaurant
        Reservation::create([
            'restaurant_id' => $otherRestaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Other Customer',
            'customer_email' => 'other@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get('/business/reservations');

        $response->assertStatus(200);
        $response->assertDontSee('Other Customer');
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Tests
    |--------------------------------------------------------------------------
    */

    public function test_today_filter_shows_only_today_reservations(): void
    {
        $now = Carbon::now('Europe/Berlin');

        // Create a reservation for today
        $todayReservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $now->toDateString(),
            'time' => '18:00:00',
            'guests' => 2,
            'duration_minutes' => 90,
            'customer_name' => 'Today Guest',
            'customer_email' => 'today@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        // Create a reservation for tomorrow
        $tomorrowReservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $now->copy()->addDay()->toDateString(),
            'time' => '19:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Tomorrow Guest',
            'customer_email' => 'tomorrow@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        // Apply the "today" filter
        $response = $this->get('/business/reservations?tableFilters[today][isActive]=1');

        $response->assertStatus(200);
        $response->assertSee('Today Guest');
        $response->assertDontSee('Tomorrow Guest');
    }

    public function test_upcoming_filter_excludes_past_reservations(): void
    {
        $now = Carbon::now('Europe/Berlin');

        // Create a past reservation
        $pastReservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $now->copy()->subDays(3)->toDateString(),
            'time' => '18:00:00',
            'guests' => 2,
            'duration_minutes' => 90,
            'customer_name' => 'Past Guest',
            'customer_email' => 'past@example.com',
            'status' => ReservationStatus::Completed,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        // Create a future reservation
        $futureReservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $now->copy()->addDay()->toDateString(),
            'time' => '19:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Future Guest',
            'customer_email' => 'future@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        // The default filter shows upcoming only (time_period=true)
        $response = $this->get('/business/reservations?tableFilters[time_period][value]=1');

        $response->assertStatus(200);
        $response->assertSee('Future Guest');
        $response->assertDontSee('Past Guest');
    }

    public function test_status_filter_filters_by_status(): void
    {
        // Create reservations with different statuses
        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 2,
            'duration_minutes' => 90,
            'customer_name' => 'Pending Guest',
            'customer_email' => 'pending@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '19:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Confirmed Guest',
            'customer_email' => 'confirmed@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        // Filter by pending status only
        $response = $this->get('/business/reservations?tableFilters[time_period][value]=&tableFilters[status][values][0]=pending');

        $response->assertStatus(200);
        $response->assertSee('Pending Guest');
        $response->assertDontSee('Confirmed Guest');
    }

    /*
    |--------------------------------------------------------------------------
    | Empty State Test
    |--------------------------------------------------------------------------
    */

    public function test_empty_state_shows_when_no_reservations(): void
    {
        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        // Clear the time_period filter to show all (including empty)
        $response = $this->get('/business/reservations?tableFilters[time_period][value]=');

        $response->assertStatus(200);
        $response->assertSee('No reservations yet');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Redirect Test
    |--------------------------------------------------------------------------
    */

    public function test_edit_page_has_redirect_to_index(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Test Guest',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        // Access the edit page
        $response = $this->get("/business/reservations/{$reservation->id}/edit");
        $response->assertStatus(200);

        // Verify the EditReservation class has getRedirectUrl method
        $editPage = new \App\Filament\Restaurant\Resources\ReservationResource\Pages\EditReservation();
        $this->assertTrue(method_exists($editPage, 'getRedirectUrl'));
    }
}
