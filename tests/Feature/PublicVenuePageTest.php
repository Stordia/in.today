<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Public Venue Profile Page (V1).
 *
 * Tests the venue profile page display including:
 * - Basic info (name, cuisine, location)
 * - Description and placeholder
 * - Opening hours display
 * - Contact information
 * - CTA buttons (book, menu)
 *
 * @group venue-page
 */
class PublicVenuePageTest extends TestCase
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
    }

    public function test_venue_profile_page_loads_for_valid_venue(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertViewIs('public.venue.show');
        $response->assertViewHas('restaurant', function ($restaurant) {
            return $restaurant->id === $this->restaurant->id;
        });
    }

    public function test_venue_profile_shows_basic_info(): void
    {
        $cuisine = Cuisine::create([
            'slug' => 'italian',
            'name_en' => 'Italian',
            'name_de' => 'Italienisch',
            'name_el' => 'Ιταλική',
        ]);

        $this->restaurant->update(['cuisine_id' => $cuisine->id]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee($this->restaurant->name);
        $response->assertSee('Italian'); // Cuisine name
        $response->assertSee('Berlin'); // City
        $response->assertSee('Germany'); // Country
    }

    public function test_venue_profile_book_tab_points_to_correct_url(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('/de/berlin/test-bistro/book', false);
        $response->assertSee('Book a table');
    }

    public function test_venue_profile_menu_tab_points_to_correct_url(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('/de/berlin/test-bistro/menu', false);
        $response->assertSee('Menu');
    }

    public function test_venue_profile_shows_opening_hours_for_bookings(): void
    {
        // Add opening hours for the week
        $hours = [
            ['day' => 0, 'open' => '17:00', 'close' => '23:00'], // Monday
            ['day' => 1, 'open' => '17:00', 'close' => '23:00'], // Tuesday
            ['day' => 2, 'open' => '17:00', 'close' => '23:00'], // Wednesday
            ['day' => 3, 'open' => '17:00', 'close' => '23:00'], // Thursday
            ['day' => 4, 'open' => '17:00', 'close' => '00:00'], // Friday
            ['day' => 5, 'open' => '17:00', 'close' => '01:00'], // Saturday
        ];

        foreach ($hours as $hour) {
            OpeningHour::create([
                'restaurant_id' => $this->restaurant->id,
                'profile' => 'booking',
                'day_of_week' => $hour['day'],
                'is_open' => true,
                'open_time' => $hour['open'],
                'close_time' => $hour['close'],
            ]);
        }

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Booking Hours');
        $response->assertSee('Monday');
        $response->assertSee('17:00');
        $response->assertSee('23:00');
        $response->assertSee('Sunday'); // Should show Sunday as closed
        $response->assertSee('Closed'); // Sunday should show as closed
    }

    public function test_venue_profile_highlights_today_in_opening_hours(): void
    {
        // Add opening hours for all days
        for ($day = 0; $day < 7; $day++) {
            OpeningHour::create([
                'restaurant_id' => $this->restaurant->id,
                'profile' => 'booking',
                'day_of_week' => $day,
                'is_open' => true,
                'open_time' => '17:00',
                'close_time' => '23:00',
            ]);
        }

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Today'); // Should show "Today" badge
    }

    public function test_venue_profile_shows_closed_today_message_when_closed(): void
    {
        // Only Monday is open
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => 0, // Monday
            'is_open' => true,
            'open_time' => '17:00',
            'close_time' => '23:00',
        ]);

        // Get current day of week (0=Monday in OpeningHour model)
        $todayDayOfWeek = (now()->dayOfWeek + 6) % 7;

        // If today is not Monday, we should see the closed message
        if ($todayDayOfWeek !== 0) {
            $response = $this->get('/de/berlin/test-bistro');

            $response->assertStatus(200);
            $response->assertSee('Closed for online bookings today');
        } else {
            // Today is Monday, so it should be open
            $this->assertTrue(true); // Skip this assertion
        }
    }

    public function test_venue_profile_shows_placeholder_if_description_missing(): void
    {
        // Restaurant created without description
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('This venue has not added a description yet');
    }

    public function test_venue_profile_shows_description_when_available(): void
    {
        $this->restaurant->update([
            'settings' => [
                'description' => 'A cozy bistro serving delicious food in the heart of Berlin.',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('A cozy bistro serving delicious food in the heart of Berlin.');
        $response->assertDontSee('This venue has not added a description yet');
    }

    public function test_venue_profile_shows_tagline_when_available(): void
    {
        $this->restaurant->update([
            'settings' => [
                'tagline' => 'Where taste meets tradition',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Where taste meets tradition');
    }

    public function test_venue_profile_shows_contact_information(): void
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
        $response->assertSee('10117');
        $response->assertSee('+49 30 12345678');
        $response->assertSee('info@test-bistro.com');
        $response->assertSee('test-bistro.com');
    }

    public function test_venue_profile_has_clickable_phone_link(): void
    {
        $this->restaurant->update([
            'settings' => [
                'phone' => '+49 30 12345678',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('tel:+49 30 12345678', false);
    }

    public function test_venue_profile_has_clickable_email_link(): void
    {
        $this->restaurant->update([
            'settings' => [
                'email' => 'info@test-bistro.com',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('mailto:info@test-bistro.com', false);
    }

    public function test_venue_profile_has_clickable_website_link(): void
    {
        $this->restaurant->update([
            'settings' => [
                'website_url' => 'https://test-bistro.com',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('https://test-bistro.com', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_venue_profile_shows_map_placeholder(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Map coming soon');
    }

    public function test_venue_profile_shows_powered_by_footer(): void
    {
        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('Powered by');
        $response->assertSee('in.today');
    }

    public function test_venue_profile_has_correct_meta_tags(): void
    {
        $this->restaurant->update([
            'settings' => [
                'tagline' => 'Best bistro in Berlin',
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('<title>Test Bistro – Berlin, Germany</title>', false);
        $response->assertSee('Best bistro in Berlin', false); // Meta description should use tagline
        $response->assertSee('index,follow', false); // SEO-friendly robots tag
    }

    public function test_venue_profile_normalizes_website_url_without_protocol(): void
    {
        $this->restaurant->update([
            'settings' => [
                'website_url' => 'test-bistro.com', // Without https://
            ],
        ]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        $response->assertSee('https://test-bistro.com', false); // Should be normalized to https://
    }

    public function test_venue_header_hides_book_tab_when_booking_disabled(): void
    {
        // Disable booking for this venue
        $this->restaurant->update(['booking_enabled' => false]);

        $response = $this->get('/de/berlin/test-bistro');

        $response->assertStatus(200);
        // Should NOT see "Book a table" tab in navigation
        $response->assertDontSee('Book a table</span>', false);
        // Should see "Online booking not available" badge instead
        $response->assertSee('Online booking not available');
    }
}
