<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantRole;
use App\Filament\Restaurant\Resources\ReservationResource;
use App\Models\City;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Business Panel reservation edit page.
 */
class BusinessReservationEditPageTest extends TestCase
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

    public function test_edit_reservation_page_is_accessible(): void
    {
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

        $response = $this->get("/business/reservations/{$reservation->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_edit_reservation_has_redirect_to_index_configured(): void
    {
        // Verify that the EditReservation page has getRedirectUrl() returning the index route
        // This ensures that after saving, users are redirected to the reservations list
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

        // Set the business panel context
        Filament::setCurrentPanel(Filament::getPanel('business'));

        // Create an instance of the EditReservation page
        $editPage = new \App\Filament\Restaurant\Resources\ReservationResource\Pages\EditReservation();

        // Use reflection to call the protected getRedirectUrl method
        $reflection = new \ReflectionMethod($editPage, 'getRedirectUrl');
        $reflection->setAccessible(true);
        $redirectUrl = $reflection->invoke($editPage);

        // The redirect URL should point to the reservations index
        $this->assertStringContainsString('reservations', $redirectUrl);
        $this->assertStringNotContainsString('/edit', $redirectUrl);
    }

    public function test_edit_page_shows_reservation_details(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '19:30:00',
            'guests' => 6,
            'duration_minutes' => 120,
            'customer_name' => 'Alice Smith',
            'customer_email' => 'alice@example.com',
            'customer_phone' => '+49123456789',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
            'language' => 'de',
        ]);

        $this->actingAs($this->restaurantOwner);
        CurrentRestaurant::set($this->restaurant->id);

        $response = $this->get("/business/reservations/{$reservation->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Alice Smith');
        $response->assertSee('alice@example.com');
    }

    public function test_reservation_resource_has_index_page(): void
    {
        // Verify the ReservationResource has the index page configured
        $pages = ReservationResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }
}
