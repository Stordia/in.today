<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for new global public venue routing system.
 *
 * Contract:
 * - URL pattern: /{countryIso2}/{citySlug}/{venueSlug}
 * - Country segment MUST be ISO2 code (lowercase) from Country.code
 * - City segment MUST be slug derived from City.name via Str::slug()
 * - Venue segment MUST match Restaurant.slug (canonical public slug)
 * - Booking pages require booking_enabled=true
 *
 * @group venue-routing
 */
class PublicVenueRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected Country $country;

    protected City $city;

    protected Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data: Germany -> Berlin -> Test Bistro
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

        $this->restaurant = Restaurant::create([
            'name' => 'Test Bistro',
            'slug' => 'test-bistro',
            'booking_enabled' => true,
            'city_id' => $this->city->id,
            'country_id' => $this->country->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
            'booking_default_duration_minutes' => 90,
        ]);

        // Add opening hours for all days (17:00 - 23:00)
        for ($day = 0; $day < 7; $day++) {
            OpeningHour::create([
                'restaurant_id' => $this->restaurant->id,
                'profile' => 'booking',
                'day_of_week' => $day,
                'is_open' => true,
                'open_time' => '17:00',
                'close_time' => '23:00',
                'last_reservation_time' => '22:00',
            ]);
        }

        // Add tables for capacity
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'min_guests' => 1,
            'max_guests' => 4,
            'is_active' => true,
        ]);

        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 2',
            'seats' => 2,
            'min_guests' => 1,
            'max_guests' => 2,
            'is_active' => true,
        ]);
    }

    public function test_venue_page_resolves_with_iso2_country_and_city_slug(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee($this->restaurant->name);
        $response->assertSee($this->city->name);
        $response->assertViewIs('public.venue.show');
        $response->assertViewHas('restaurant', function ($restaurant) {
            return $restaurant->id === $this->restaurant->id;
        });
    }

    public function test_booking_page_loads_for_valid_venue(): void
    {
        $response = $this->get('/de/berlin/test-bistro/book');

        $response->assertStatus(200);
        $response->assertSee($this->restaurant->name);
        $response->assertViewIs('public.booking');
        $response->assertViewHas('restaurant', function ($restaurant) {
            return $restaurant->id === $this->restaurant->id;
        });
    }

    public function test_menu_page_loads_for_valid_venue(): void
    {
        $response = $this->get('/de/berlin/test-bistro/menu');

        $response->assertStatus(200);
        $response->assertSee($this->restaurant->name);
        $response->assertSee('Menu');
        $response->assertViewIs('public.venue.menu');
    }

    public function test_booking_submission_creates_reservation_for_valid_route(): void
    {
        $date = now()->addDays(3)->format('Y-m-d');
        $time = '19:00';

        $response = $this->post('/de/berlin/test-bistro/book', [
            'date' => $date,
            'time' => $time,
            'party_size' => 2,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+49123456789',
            'notes' => 'Window seat please',
            'accepted_terms' => '1',
            'hp_website' => '', // honeypot field
        ]);

        $response->assertRedirect('/de/berlin/test-bistro/book');
        $response->assertSessionHas('booking_status', 'success');

        $this->assertDatabaseHas('reservations', [
            'restaurant_id' => $this->restaurant->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'guests' => 2,
            'source' => 'widget',
        ]);

        $reservation = Reservation::where('customer_email', 'john@example.com')->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($date, $reservation->date->format('Y-m-d'));
    }

    public function test_wrong_country_segment_returns_404(): void
    {
        // Restaurant is in DE (Germany), but call with FR (France)
        // This should return 404, NOT redirect
        $response = $this->get('/fr/berlin/test-bistro');

        $response->assertStatus(404);
    }

    public function test_wrong_city_slug_redirects_to_canonical_city_slug(): void
    {
        // City name "Berlin" should have slug "berlin"
        // Call with wrong slug "berlinn" (typo)
        // This should 301 redirect to canonical URL
        $response = $this->get('/de/berlinn/test-bistro');

        $response->assertRedirect('/de/berlin/test-bistro');
        $response->assertStatus(301);
    }

    public function test_city_mismatch_redirects_for_booking_page(): void
    {
        $response = $this->get('/de/berlinn/test-bistro/book');

        $response->assertRedirect('/de/berlin/test-bistro/book');
        $response->assertStatus(301);
    }

    public function test_city_mismatch_redirects_for_menu_page(): void
    {
        $response = $this->get('/de/berlinn/test-bistro/menu');

        $response->assertRedirect('/de/berlin/test-bistro/menu');
        $response->assertStatus(301);
    }

    public function test_unknown_venue_slug_returns_404(): void
    {
        $response = $this->get('/de/berlin/non-existing');

        $response->assertStatus(404);
    }

    public function test_venue_page_works_even_when_booking_disabled(): void
    {
        // Venue page (profile) should work even when booking is disabled
        $this->restaurant->update(['booking_enabled' => false]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee($this->restaurant->name);
    }

    public function test_booking_page_returns_404_when_booking_disabled(): void
    {
        // Booking page specifically requires booking_enabled=true
        $this->restaurant->update(['booking_enabled' => false]);

        $response = $this->get('/de/berlin/test-bistro/book');

        $response->assertStatus(404);
    }

    public function test_booking_page_accepts_date_and_party_size_parameters(): void
    {
        $date = now()->addDays(5)->format('Y-m-d');

        $response = $this->get('/de/berlin/test-bistro/book?date=' . $date . '&party_size=4');

        $response->assertStatus(200);
        $response->assertViewHas('date', $date);
        $response->assertViewHas('partySize', 4);
    }

    public function test_booking_submission_validates_required_fields(): void
    {
        $response = $this->post('/de/berlin/test-bistro/book', [
            'date' => now()->addDays(3)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
            // Missing required fields
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'accepted_terms']);
    }

    public function test_booking_submission_validates_party_size_range(): void
    {
        $date = now()->addDays(3)->format('Y-m-d');

        // Too small
        $response = $this->post('/de/berlin/test-bistro/book', [
            'date' => $date,
            'time' => '19:00',
            'party_size' => 0,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'accepted_terms' => '1',
        ]);

        $response->assertSessionHasErrors(['party_size']);

        // Too large
        $response = $this->post('/de/berlin/test-bistro/book', [
            'date' => $date,
            'time' => '19:00',
            'party_size' => 100,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'accepted_terms' => '1',
        ]);

        $response->assertSessionHasErrors(['party_size']);
    }

    public function test_honeypot_triggers_fake_success(): void
    {
        $date = now()->addDays(3)->format('Y-m-d');

        $response = $this->post('/de/berlin/test-bistro/book', [
            'date' => $date,
            'time' => '19:00',
            'party_size' => 2,
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'accepted_terms' => '1',
            'hp_website' => 'http://spam.com', // Honeypot triggered
        ]);

        $response->assertRedirect('/de/berlin/test-bistro/book');
        $response->assertSessionHas('booking_status', 'success');

        // But no reservation should be created
        $this->assertDatabaseMissing('reservations', [
            'customer_email' => 'spam@example.com',
        ]);
    }

    public function test_venue_page_displays_contact_information(): void
    {
        $this->restaurant->update([
            'address_street' => 'Friedrichstraße 123',
            'address_postal' => '10117',
            'settings' => [
                'phone' => '+49 30 12345678',
                'email' => 'info@test-bistro.com',
                'website_url' => 'https://test-bistro.com',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Friedrichstraße 123');
        $response->assertSee('+49 30 12345678');
        $response->assertSee('info@test-bistro.com');
        $response->assertSee('test-bistro.com');
    }

    public function test_venue_page_has_booking_and_menu_links(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('/de/berlin/test-bistro/book', false);
        $response->assertSee('/de/berlin/test-bistro/menu', false);
    }

    public function test_lowercase_country_code_is_required(): void
    {
        // Uppercase country code should not work
        $response = $this->get('/DE/berlin/test-bistro');

        $response->assertStatus(404);
    }

    public function test_venue_slug_with_dots_is_allowed(): void
    {
        // Update the restaurant to have a slug with dots to test this edge case
        $this->restaurant->update(['slug' => 'test.bistro']);

        // Debug: check if data exists
        $this->assertDatabaseHas('countries', ['code' => 'DE']);
        $this->assertDatabaseHas('cities', ['name' => 'Berlin']);
        $this->assertDatabaseHas('restaurants', ['slug' => 'test.bistro']);

        $response = $this->get('/de/berlin/test.bistro');

        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200);
    }

    public function test_booking_page_has_route_context_variables(): void
    {
        $response = $this->get('/de/berlin/test-bistro/book');

        $response->assertStatus(200);
        $response->assertViewHas('country', 'de');
        $response->assertViewHas('city', 'berlin');
        $response->assertViewHas('venue', 'test-bistro');
    }
}
