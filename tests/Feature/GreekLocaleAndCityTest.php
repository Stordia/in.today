<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Greek locale /gr and city discovery for Greece.
 */
class GreekLocaleAndCityTest extends TestCase
{
    use RefreshDatabase;

    protected Country $greece;

    protected City $athens;

    protected Restaurant $athensRestaurant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data: Greece -> Athens -> Test Restaurant
        $this->greece = Country::firstOrCreate(
            ['code' => 'GR'],
            [
                'name' => 'Greece',
                'slug' => 'greece',
                'is_active' => true,
            ]
        );

        $this->athens = City::create([
            'name' => 'Athens',
            'slug' => 'athens',
            'country_id' => $this->greece->id,
            'is_active' => true,
        ]);

        $this->athensRestaurant = Restaurant::create([
            'name' => 'Athens Taverna',
            'slug' => 'athens-taverna',
            'booking_enabled' => true,
            'city_id' => $this->athens->id,
            'country_id' => $this->greece->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);
    }

    public function test_gr_locale_landing_page_loads_with_greek_content(): void
    {
        $response = $this->get('/gr');

        $response->assertStatus(200);
        // Greek landing page should contain Greek text, not English
        // Check for a Greek-specific string from the landing page
        $response->assertDontSee('Features'); // English word
    }

    public function test_el_redirects_to_gr(): void
    {
        $response = $this->get('/el');

        $response->assertStatus(301);
        $response->assertRedirect('/gr');
    }

    public function test_el_with_path_redirects_to_gr_with_path(): void
    {
        $response = $this->get('/el/contact');

        $response->assertStatus(301);
        $response->assertRedirect('/gr/contact');
    }

    public function test_city_results_page_for_athens(): void
    {
        $response = $this->get('/gr/athens');

        $response->assertStatus(200);
        $response->assertViewIs('public.city.show');
        $response->assertSee('Athens, Greece');
        $response->assertSee('Athens Taverna');
    }

    public function test_city_results_page_canonical_slug_redirect(): void
    {
        // Update city name to have mixed case so slug differs from name
        $this->athens->update(['name' => 'Athens City']);
        $this->athens->refresh();

        // Access with non-canonical slug 'athens' (city name is 'Athens City', slug should be 'athens-city')
        $response = $this->get('/gr/athens');

        // Should redirect to canonical slug derived from current name
        $response->assertStatus(301);
        $response->assertRedirect('/gr/athens-city');
    }

    public function test_venue_page_works_for_greek_venue(): void
    {
        $response = $this->get('/gr/athens/athens-taverna');

        $response->assertStatus(200);
        $response->assertViewIs('public.venue.show');
        $response->assertSee('Athens Taverna');
    }

    public function test_city_with_legacy_slug_suffix_redirects_to_canonical(): void
    {
        // Create a city with legacy slug format (name-countrycode)
        $katerini = City::create([
            'name' => 'Katerini',
            'slug' => 'katerini-gr', // Legacy slug with country suffix
            'country_id' => $this->greece->id,
            'is_active' => true,
        ]);

        // Add a restaurant to make the city discoverable
        Restaurant::create([
            'name' => 'Katerini Restaurant',
            'slug' => 'katerini-restaurant',
            'booking_enabled' => true,
            'city_id' => $katerini->id,
            'country_id' => $this->greece->id,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 10,
        ]);

        // Test legacy slug redirects to canonical
        $response = $this->get('/gr/katerini-gr');
        $response->assertStatus(301);
        $response->assertRedirect('/gr/katerini');

        // Test canonical slug works
        $response = $this->get('/gr/katerini');
        $response->assertStatus(200);
        $response->assertSee('Katerini, Greece');
        $response->assertSee('Katerini Restaurant');
    }
}
