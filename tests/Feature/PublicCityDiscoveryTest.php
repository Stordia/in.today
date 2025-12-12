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
 * Tests for Public City Discovery Pages.
 *
 * Tests the home page with city search and city results pages.
 *
 * @group city-discovery
 */
class PublicCityDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected Country $germany;

    protected Country $france;

    protected City $berlin;

    protected City $paris;

    protected Restaurant $berlinRestaurant;

    protected Restaurant $parisRestaurant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data: Germany -> Berlin and France -> Paris
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

        // Create restaurants with booking enabled
        $this->berlinRestaurant = Restaurant::create([
            'name' => 'Berlin Bistro',
            'slug' => 'berlin-bistro',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $this->parisRestaurant = Restaurant::create([
            'name' => 'Paris Cafe',
            'slug' => 'paris-cafe',
            'booking_enabled' => true,
            'city_id' => $this->paris->id,
            'country_id' => $this->france->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);
    }

    public function test_home_page_loads_and_shows_city_search(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertSee('Find a place for tonight');
        $response->assertSee('Where do you want to go?');
        $response->assertSee('Find places');
    }

    public function test_home_page_lists_cities_with_bookable_venues(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Berlin, Germany');
        $response->assertSee('Paris, France');
    }

    public function test_home_page_does_not_list_cities_without_bookable_venues(): void
    {
        // Create a city without any restaurants
        $emptyCity = City::create([
            'name' => 'Munich',
            'slug' => 'munich',
            'country_id' => $this->germany->id,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Munich, Germany');
    }

    public function test_home_page_shows_empty_state_when_no_cities(): void
    {
        // Delete all restaurants to make cities invisible
        Restaurant::query()->delete();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('No cities available yet');
    }

    public function test_submitting_city_redirects_to_city_url(): void
    {
        $response = $this->get('/search?city_id=' . $this->berlin->id);

        $response->assertRedirect('/de/berlin');
    }

    public function test_submitting_invalid_city_redirects_to_home(): void
    {
        $response = $this->get('/search?city_id=99999');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Please select a valid city.');
    }

    public function test_city_results_page_shows_venues_for_that_city(): void
    {
        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertViewIs('public.city.show');
        $response->assertSee('Places in Berlin, Germany');
        $response->assertSee('Berlin Bistro');
        $response->assertDontSee('Paris Cafe'); // Should not show venues from other cities
    }

    public function test_city_results_page_shows_multiple_venues_in_same_city(): void
    {
        // Add another restaurant in Berlin
        Restaurant::create([
            'name' => 'Another Berlin Venue',
            'slug' => 'another-berlin-venue',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('2 venues');
        $response->assertSee('Berlin Bistro');
        $response->assertSee('Another Berlin Venue');
    }

    public function test_city_results_page_uses_canonical_city_slug(): void
    {
        // Try accessing with wrong slug
        $response = $this->get('/de/wrong-slug');

        // Should 404 because city doesn't exist
        $response->assertStatus(404);
    }

    public function test_city_results_page_redirects_to_canonical_slug(): void
    {
        // Update Berlin's name so slug changes
        $this->berlin->update(['name' => 'Berlin Updated']);
        $this->berlin->refresh();

        // Access with old slug
        $response = $this->get('/de/berlin');

        // Should redirect to new canonical slug
        $response->assertRedirect('/de/berlin-updated');
    }

    public function test_city_results_page_validates_country_code(): void
    {
        // Try accessing Berlin with wrong country code
        $response = $this->get('/fr/berlin');

        // Should 404 because country doesn't match
        $response->assertStatus(404);
    }

    public function test_city_results_page_redirects_legacy_slug_pattern_to_canonical(): void
    {
        // Create a city with legacy slug pattern: athens-gr
        $greece = Country::firstOrCreate(
            ['code' => 'GR'],
            ['name' => 'Greece', 'slug' => 'greece', 'is_active' => true]
        );

        $athens = City::create([
            'name' => 'Athens',
            'slug' => 'athens-gr', // legacy pattern
            'slug_canonical' => 'athens', // canonical is just city name
            'country_id' => $greece->id,
            'is_active' => true,
        ]);

        // Add a restaurant so the city shows up
        Restaurant::create([
            'name' => 'Athens Restaurant',
            'slug' => 'athens-restaurant',
            'booking_enabled' => true,
            'city_id' => $athens->id,
            'country_id' => $greece->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Access with legacy URL pattern /gr/athens-gr
        $response = $this->get('/gr/athens-gr');

        // Should 301 redirect to canonical /gr/athens
        $response->assertRedirect('/gr/athens');
        $response->assertStatus(301);
    }

    public function test_city_results_page_works_with_canonical_slug(): void
    {
        // Create a city with proper canonical slug
        $greece = Country::firstOrCreate(
            ['code' => 'GR'],
            ['name' => 'Greece', 'slug' => 'greece', 'is_active' => true]
        );

        $athens = City::create([
            'name' => 'Athens',
            'slug' => 'athens-gr', // legacy
            'slug_canonical' => 'athens', // canonical
            'country_id' => $greece->id,
            'is_active' => true,
        ]);

        Restaurant::create([
            'name' => 'Athens Restaurant',
            'slug' => 'athens-restaurant',
            'booking_enabled' => true,
            'city_id' => $athens->id,
            'country_id' => $greece->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Access with canonical URL /gr/athens
        $response = $this->get('/gr/athens');

        // Should work without redirect
        $response->assertStatus(200);
        $response->assertSee('Athens');
        $response->assertSee('Athens Restaurant');
    }

    public function test_country_code_must_be_lowercase_in_url(): void
    {
        // Access Berlin with uppercase country code
        $response = $this->get('/DE/berlin');

        // Should 404 because route constraint requires lowercase
        $response->assertStatus(404);

        // Lowercase version works
        $response = $this->get('/de/berlin');
        $response->assertStatus(200);
        $response->assertSee('Berlin');
    }

    public function test_city_results_page_shows_empty_state_if_no_venues(): void
    {
        // Create a city without restaurants
        $emptyCity = City::create([
            'name' => 'Munich',
            'slug' => 'munich',
            'country_id' => $this->germany->id,
            'is_active' => true,
        ]);

        $response = $this->get('/de/munich');

        $response->assertStatus(200);
        $response->assertSee('No venues found');
        $response->assertSee('No venues match your current filters');
        $response->assertSee('Back to search');
    }

    public function test_city_results_page_shows_all_venues_by_default(): void
    {
        // Create a restaurant with booking disabled
        Restaurant::create([
            'name' => 'No Booking Restaurant',
            'slug' => 'no-booking-restaurant',
            'booking_enabled' => false,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
        ]);

        // Default should show ALL venues (booking is optional)
        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('Berlin Bistro'); // booking enabled
        $response->assertSee('No Booking Restaurant'); // booking disabled
    }

    public function test_city_results_page_shows_venue_details(): void
    {
        // Add cuisine and tagline to the restaurant
        $cuisine = Cuisine::create([
            'slug' => 'italian',
            'name_en' => 'Italian',
            'name_de' => 'Italienisch',
            'name_el' => 'Ιταλική',
        ]);

        $this->berlinRestaurant->update([
            'cuisine_id' => $cuisine->id,
            'settings' => [
                'tagline' => 'Best Italian food in Berlin',
            ],
        ]);

        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('Berlin Bistro');
        $response->assertSee('Italian');
        $response->assertSee('Best Italian food in Berlin');
    }

    public function test_city_results_page_has_links_to_venue_pages(): void
    {
        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('/de/berlin/berlin-bistro', false); // View link
        $response->assertSee('/de/berlin/berlin-bistro/book', false); // Book link
    }

    public function test_city_results_page_sorts_venues_alphabetically(): void
    {
        // Create restaurants with names that sort differently
        Restaurant::create([
            'name' => 'Zebra Restaurant',
            'slug' => 'zebra-restaurant',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        Restaurant::create([
            'name' => 'Alpha Restaurant',
            'slug' => 'alpha-restaurant',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Alpha Restaurant', 'Berlin Bistro', 'Zebra Restaurant']);
    }

    public function test_venue_routes_still_work_after_city_routes(): void
    {
        // Make sure venue routes still work and don't conflict with city routes
        $response = $this->get('/de/berlin/berlin-bistro');

        $response->assertStatus(200);
        $response->assertViewIs('public.venue.show');
    }

    public function test_city_page_shows_filter_ui(): void
    {
        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('Cuisine');
        $response->assertSee('All cuisines');
        $response->assertSee('Online booking available');
        $response->assertSee('Open today');
        $response->assertSee('Apply');
        $response->assertSee('Clear');
    }

    public function test_applying_cuisine_filter_stores_session_and_filters_results(): void
    {
        // Create cuisines
        $italian = Cuisine::create(['name_en' => 'Italian', 'sort_order' => 1]);
        $german = Cuisine::create(['name_en' => 'German', 'sort_order' => 2]);

        // Create restaurants with different cuisines
        Restaurant::create([
            'name' => 'Italian Place',
            'slug' => 'italian-place',
            'booking_enabled' => true,
            'cuisine_id' => $italian->id,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        Restaurant::create([
            'name' => 'German Place',
            'slug' => 'german-place',
            'booking_enabled' => true,
            'cuisine_id' => $german->id,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Apply filter
        $response = $this->post('/de/berlin/filters', [
            'cuisine_id' => $italian->id,
            'booking_only' => false,
            'open_today' => false,
        ]);

        $response->assertRedirect('/de/berlin');
        $this->assertNotEmpty(session("public-city-filters:de:{$this->berlin->id}"));

        // Visit page and check filtered results
        $response = $this->get('/de/berlin');
        $response->assertStatus(200);
        $response->assertSee('Italian Place');
        $response->assertDontSee('German Place');
    }

    public function test_open_today_filter_excludes_closed_venues(): void
    {
        // Create a venue with opening hours for today
        $openVenue = Restaurant::create([
            'name' => 'Open Today Venue',
            'slug' => 'open-today',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $closedVenue = Restaurant::create([
            'name' => 'Closed Today Venue',
            'slug' => 'closed-today',
            'booking_enabled' => true,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Get today's day of week (0=Monday in our system)
        $timezone = config('app.timezone', 'UTC');
        $now = now($timezone);
        $todayDayOfWeek = ($now->dayOfWeek + 6) % 7;

        // Add opening hours for open venue (today is open)
        \App\Models\OpeningHour::create([
            'restaurant_id' => $openVenue->id,
            'profile' => 'booking',
            'day_of_week' => $todayDayOfWeek,
            'is_open' => true,
            'open_time' => '10:00',
            'close_time' => '22:00',
            'last_reservation_time' => '21:00',
        ]);

        // Closed venue has no opening hours for today

        // Apply open today filter
        $response = $this->post('/de/berlin/filters', [
            'cuisine_id' => '',
            'booking_only' => false,
            'open_today' => true,
        ]);

        $response->assertRedirect('/de/berlin');

        // Check results
        $response = $this->get('/de/berlin');
        $response->assertStatus(200);
        $response->assertSee('Open Today Venue');
        $response->assertDontSee('Closed Today Venue');
    }

    public function test_booking_only_filter_excludes_non_bookable_venues(): void
    {
        // Create a restaurant with booking disabled
        Restaurant::create([
            'name' => 'No Booking Restaurant',
            'slug' => 'no-booking-restaurant',
            'booking_enabled' => false,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
        ]);

        // Apply booking_only filter
        $response = $this->post('/de/berlin/filters', [
            'cuisine_id' => '',
            'booking_only' => true,
            'open_today' => false,
        ]);

        $response->assertRedirect('/de/berlin');

        // Check results - should only show bookable venues
        $response = $this->get('/de/berlin');
        $response->assertStatus(200);
        $response->assertSee('Berlin Bistro'); // booking enabled
        $response->assertDontSee('No Booking Restaurant'); // booking disabled
    }

    public function test_clear_filters_resets_to_default_list(): void
    {
        // Create cuisines
        $italian = Cuisine::create(['name_en' => 'Italian', 'sort_order' => 1]);

        // Create restaurants
        Restaurant::create([
            'name' => 'Italian Place',
            'slug' => 'italian-place',
            'booking_enabled' => true,
            'cuisine_id' => $italian->id,
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Apply filter
        $this->post('/de/berlin/filters', [
            'cuisine_id' => $italian->id,
            'booking_only' => false,
            'open_today' => false,
        ]);

        // Verify filter is in session
        $this->assertNotEmpty(session("public-city-filters:de:{$this->berlin->id}"));

        // Clear filters
        $response = $this->post('/de/berlin/filters/clear');
        $response->assertRedirect('/de/berlin');

        // Verify filter is cleared from session
        $this->assertEmpty(session("public-city-filters:de:{$this->berlin->id}"));

        // Check page shows all venues
        $response = $this->get('/de/berlin');
        $response->assertStatus(200);
        $response->assertSee('Italian Place');
        $response->assertSee('Berlin Bistro'); // Original test fixture
    }
}
