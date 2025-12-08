<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GlobalRole;
use App\Filament\Resources\RestaurantResource\Pages\CreateRestaurant;
use App\Filament\Resources\RestaurantResource\Pages\EditRestaurant;
use App\Filament\Resources\RestaurantResource\Pages\OnboardRestaurant;
use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantBookingSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Country $country;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

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
    }

    public function test_restaurant_can_be_created_with_booking_settings(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_public_slug' => 'test-restaurant-abc123',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 10,
            'booking_default_duration_minutes' => 90,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
        ]);

        $this->assertTrue($restaurant->booking_enabled);
        $this->assertEquals('test-restaurant-abc123', $restaurant->booking_public_slug);
        $this->assertEquals(2, $restaurant->booking_min_party_size);
        $this->assertEquals(10, $restaurant->booking_max_party_size);
        $this->assertEquals(90, $restaurant->booking_default_duration_minutes);
        $this->assertEquals(60, $restaurant->booking_min_lead_time_minutes);
        $this->assertEquals(30, $restaurant->booking_max_lead_time_days);
    }

    public function test_restaurant_booking_settings_can_be_updated(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => false,
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 6,
        ]);

        $restaurant->update([
            'booking_enabled' => true,
            'booking_public_slug' => 'updated-slug',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 12,
        ]);

        $restaurant->refresh();

        $this->assertTrue($restaurant->booking_enabled);
        $this->assertEquals('updated-slug', $restaurant->booking_public_slug);
        $this->assertEquals(2, $restaurant->booking_min_party_size);
        $this->assertEquals(12, $restaurant->booking_max_party_size);
    }

    public function test_booking_enabled_helper_method_works(): void
    {
        $enabledRestaurant = Restaurant::create([
            'name' => 'Enabled Restaurant',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
        ]);

        $disabledRestaurant = Restaurant::create([
            'name' => 'Disabled Restaurant',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => false,
        ]);

        $this->assertTrue($enabledRestaurant->isBookingEnabled());
        $this->assertFalse($disabledRestaurant->isBookingEnabled());
    }

    public function test_booking_url_helper_returns_correct_url(): void
    {
        $restaurantWithSlug = Restaurant::create([
            'name' => 'Restaurant With Slug',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_public_slug' => 'my-restaurant',
        ]);

        $restaurantWithoutSlug = Restaurant::create([
            'name' => 'Restaurant Without Slug',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_public_slug' => null,
        ]);

        $this->assertEquals(url('/book/my-restaurant'), $restaurantWithSlug->getBookingUrl());
        $this->assertNull($restaurantWithoutSlug->getBookingUrl());
    }

    public function test_onboard_restaurant_creates_restaurant_with_correct_booking_defaults(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OnboardRestaurant::class)
            ->set('data.name', 'New Onboarded Restaurant')
            ->set('data.country_id', $this->country->id)
            ->set('data.city_id', $this->city->id)
            ->set('data.timezone', 'Europe/Berlin')
            ->set('data.booking_enabled', true)
            ->set('data.booking_min_party_size', 2)
            ->set('data.booking_max_party_size', 8)
            ->set('data.booking_default_duration_minutes', 90)
            ->set('data.booking_min_lead_time_minutes', 60)
            ->set('data.booking_max_lead_time_days', 30)
            ->set('data.owner_mode', 'new_user')
            ->set('data.owner_name', 'Restaurant Owner')
            ->set('data.owner_email', 'owner@restaurant.com')
            ->call('create');

        $restaurant = Restaurant::where('name', 'New Onboarded Restaurant')->first();

        $this->assertNotNull($restaurant);
        $this->assertTrue($restaurant->booking_enabled);
        $this->assertNotNull($restaurant->booking_public_slug);
        $this->assertStringContainsString('new-onboarded-restaurant', $restaurant->booking_public_slug);
        $this->assertEquals(2, $restaurant->booking_min_party_size);
        $this->assertEquals(8, $restaurant->booking_max_party_size);
        $this->assertEquals(90, $restaurant->booking_default_duration_minutes);
        $this->assertEquals(60, $restaurant->booking_min_lead_time_minutes);
        $this->assertEquals(30, $restaurant->booking_max_lead_time_days);
    }

    public function test_onboard_restaurant_uses_provided_booking_slug_when_given(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OnboardRestaurant::class)
            ->set('data.name', 'Custom Slug Restaurant')
            ->set('data.country_id', $this->country->id)
            ->set('data.city_id', $this->city->id)
            ->set('data.timezone', 'Europe/Berlin')
            ->set('data.booking_enabled', true)
            ->set('data.booking_public_slug', 'my-custom-slug')
            ->set('data.booking_min_party_size', 2)
            ->set('data.booking_max_party_size', 8)
            ->set('data.booking_default_duration_minutes', 90)
            ->set('data.booking_min_lead_time_minutes', 60)
            ->set('data.booking_max_lead_time_days', 30)
            ->set('data.owner_mode', 'new_user')
            ->set('data.owner_name', 'Restaurant Owner')
            ->set('data.owner_email', 'owner2@restaurant.com')
            ->call('create');

        $restaurant = Restaurant::where('name', 'Custom Slug Restaurant')->first();

        $this->assertNotNull($restaurant);
        $this->assertEquals('my-custom-slug', $restaurant->booking_public_slug);
    }

    public function test_onboard_restaurant_does_not_set_slug_when_booking_disabled(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OnboardRestaurant::class)
            ->set('data.name', 'No Booking Restaurant')
            ->set('data.country_id', $this->country->id)
            ->set('data.city_id', $this->city->id)
            ->set('data.timezone', 'Europe/Berlin')
            ->set('data.booking_enabled', false)
            ->set('data.booking_min_party_size', 2)
            ->set('data.booking_max_party_size', 8)
            ->set('data.booking_default_duration_minutes', 90)
            ->set('data.booking_min_lead_time_minutes', 60)
            ->set('data.booking_max_lead_time_days', 30)
            ->set('data.owner_mode', 'new_user')
            ->set('data.owner_name', 'Restaurant Owner')
            ->set('data.owner_email', 'owner3@restaurant.com')
            ->call('create');

        $restaurant = Restaurant::where('name', 'No Booking Restaurant')->first();

        $this->assertNotNull($restaurant);
        $this->assertFalse($restaurant->booking_enabled);
        $this->assertNull($restaurant->booking_public_slug);
    }

    public function test_onboard_restaurant_validates_max_party_size_greater_than_min(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OnboardRestaurant::class)
            ->set('data.name', 'Invalid Party Size Restaurant')
            ->set('data.country_id', $this->country->id)
            ->set('data.city_id', $this->city->id)
            ->set('data.timezone', 'Europe/Berlin')
            ->set('data.booking_enabled', true)
            ->set('data.booking_min_party_size', 10)
            ->set('data.booking_max_party_size', 5)
            ->set('data.booking_default_duration_minutes', 90)
            ->set('data.booking_min_lead_time_minutes', 60)
            ->set('data.booking_max_lead_time_days', 30)
            ->set('data.owner_mode', 'new_user')
            ->set('data.owner_name', 'Restaurant Owner')
            ->set('data.owner_email', 'owner4@restaurant.com')
            ->call('create')
            ->assertHasErrors(['data.booking_max_party_size']);
    }

    public function test_edit_restaurant_auto_generates_slug_when_empty(): void
    {
        $this->actingAs($this->admin);

        $restaurant = Restaurant::create([
            'name' => 'Restaurant Without Slug',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => false,
            'booking_public_slug' => null,
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 8,
        ]);

        Livewire::test(EditRestaurant::class, ['record' => $restaurant->id])
            ->set('data.booking_enabled', true)
            ->set('data.booking_public_slug', '')
            ->call('save');

        $restaurant->refresh();

        $this->assertTrue($restaurant->booking_enabled);
        $this->assertNotNull($restaurant->booking_public_slug);
        $this->assertStringContainsString('restaurant-without-slug', $restaurant->booking_public_slug);
    }

    public function test_edit_restaurant_preserves_existing_slug(): void
    {
        $this->actingAs($this->admin);

        $restaurant = Restaurant::create([
            'name' => 'Restaurant With Slug',
            'city_id' => $this->city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_public_slug' => 'existing-slug',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 8,
        ]);

        Livewire::test(EditRestaurant::class, ['record' => $restaurant->id])
            ->set('data.booking_public_slug', '')
            ->call('save');

        $restaurant->refresh();

        // Should preserve the existing slug even when form field is empty
        $this->assertEquals('existing-slug', $restaurant->booking_public_slug);
    }

    public function test_create_restaurant_page_auto_generates_slug_logic(): void
    {
        // Test the slug generation logic directly without going through the full form
        // (the CreateRestaurant form has many required fields beyond scope of booking tests)

        $page = new CreateRestaurant;

        // Use reflection to test the mutateFormDataBeforeCreate method
        $method = new \ReflectionMethod(CreateRestaurant::class, 'mutateFormDataBeforeCreate');
        $method->setAccessible(true);

        // Test: booking enabled with empty slug should auto-generate
        $data = [
            'name' => 'Test Restaurant Name',
            'booking_enabled' => true,
            'booking_public_slug' => '',
        ];

        $result = $method->invoke($page, $data);

        $this->assertNotEmpty($result['booking_public_slug']);
        $this->assertStringContainsString('test-restaurant-name', $result['booking_public_slug']);

        // Test: booking disabled should not generate slug
        $data2 = [
            'name' => 'Another Restaurant',
            'booking_enabled' => false,
            'booking_public_slug' => '',
        ];

        $result2 = $method->invoke($page, $data2);

        $this->assertEmpty($result2['booking_public_slug']);

        // Test: provided slug should be preserved
        $data3 = [
            'name' => 'Third Restaurant',
            'booking_enabled' => true,
            'booking_public_slug' => 'custom-slug',
        ];

        $result3 = $method->invoke($page, $data3);

        $this->assertEquals('custom-slug', $result3['booking_public_slug']);
    }
}
