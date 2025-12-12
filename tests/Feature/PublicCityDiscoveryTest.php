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
            'booking_public_slug' => 'berlin-bistro',
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $this->parisRestaurant = Restaurant::create([
            'name' => 'Paris Cafe',
            'slug' => 'paris-cafe',
            'booking_enabled' => true,
            'booking_public_slug' => 'paris-cafe',
            'city_id' => $this->paris->id,
            'country_id' => $this->france->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);
    }

    public function test_home_page_loads_and_shows_city_search(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertSee('Find a place for tonight');
        $response->assertSee('Where do you want to go?');
        $response->assertSee('Find places');
    }

    public function test_home_page_lists_cities_with_bookable_venues(): void
    {
        $response = $this->get('/home');

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

        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertDontSee('Munich, Germany');
    }

    public function test_home_page_shows_empty_state_when_no_cities(): void
    {
        // Delete all restaurants to make cities invisible
        Restaurant::query()->delete();

        $response = $this->get('/home');

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

        $response->assertRedirect('/home');
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
            'booking_public_slug' => 'another-berlin-venue',
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('2 venues with online booking');
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
        $response->assertSee('No venues available yet');
        $response->assertSee('No venues with online booking in this city yet');
        $response->assertSee('Back to search');
    }

    public function test_city_results_page_only_shows_venues_with_booking_enabled(): void
    {
        // Create a restaurant with booking disabled
        Restaurant::create([
            'name' => 'Disabled Berlin Restaurant',
            'slug' => 'disabled-berlin-restaurant',
            'booking_enabled' => false,
            'booking_public_slug' => 'disabled-berlin-restaurant',
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
        ]);

        $response = $this->get('/de/berlin');

        $response->assertStatus(200);
        $response->assertSee('Berlin Bistro');
        $response->assertDontSee('Disabled Berlin Restaurant');
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
        $response->assertSee('Online booking');
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
            'booking_public_slug' => 'zebra-restaurant',
            'city_id' => $this->berlin->id,
            'country_id' => $this->germany->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        Restaurant::create([
            'name' => 'Alpha Restaurant',
            'slug' => 'alpha-restaurant',
            'booking_enabled' => true,
            'booking_public_slug' => 'alpha-restaurant',
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
}
