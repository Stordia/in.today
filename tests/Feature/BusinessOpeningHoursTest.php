<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GlobalRole;
use App\Enums\RestaurantRole;
use App\Filament\Restaurant\Pages\ManageOpeningHours;
use App\Models\BlockedDate;
use App\Models\City;
use App\Models\Country;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessOpeningHoursTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->owner = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'owner@restaurant.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::User,
        ]);

        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'city_id' => $city->id,
            'timezone' => 'Europe/Berlin',
            'booking_enabled' => true,
            'booking_public_slug' => 'test-restaurant',
        ]);

        RestaurantUser::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->owner->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);

        session(['current_restaurant_id' => $this->restaurant->id]);
    }

    public function test_business_opening_hours_page_loads_for_owner(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(ManageOpeningHours::class)
            ->assertSuccessful();
    }

    public function test_business_can_update_weekly_booking_hours(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(ManageOpeningHours::class)
            ->set('data.day_0_is_open', true)
            ->set('data.day_0_open_time', '12:00')
            ->set('data.day_0_close_time', '22:00')
            ->set('data.day_1_is_open', true)
            ->set('data.day_1_open_time', '12:00')
            ->set('data.day_1_close_time', '22:00')
            ->call('save')
            ->assertHasNoErrors();

        // Verify Monday (day 0) is saved with booking profile
        $monday = OpeningHour::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->bookingProfile()
            ->forDay(0)
            ->first();

        $this->assertNotNull($monday);
        $this->assertTrue($monday->is_open);
        $this->assertEquals('booking', $monday->profile);
        $this->assertEquals('12:00', $monday->open_time instanceof Carbon ? $monday->open_time->format('H:i') : $monday->open_time);
        $this->assertEquals('22:00', $monday->close_time instanceof Carbon ? $monday->close_time->format('H:i') : $monday->close_time);

        // Verify Tuesday (day 1) is saved with booking profile
        $tuesday = OpeningHour::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->bookingProfile()
            ->forDay(1)
            ->first();

        $this->assertNotNull($tuesday);
        $this->assertEquals('booking', $tuesday->profile);
    }

    public function test_business_can_add_blocked_date_for_bookings(): void
    {
        $this->actingAs($this->owner);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::test(ManageOpeningHours::class)
            ->set('data.blocked_dates.0.date', $tomorrow)
            ->set('data.blocked_dates.0.reason', 'Christmas Holiday')
            ->call('save')
            ->assertHasNoErrors();

        // Verify blocked date is saved with booking profile
        $blocked = BlockedDate::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->bookingProfile()
            ->whereDate('date', $tomorrow)
            ->first();

        $this->assertNotNull($blocked);
        $this->assertEquals('booking', $blocked->profile);
        $this->assertTrue($blocked->is_all_day);
        $this->assertEquals('Christmas Holiday', $blocked->reason);
    }

    public function test_blocked_date_prevents_availability_for_that_day(): void
    {
        // Create opening hours for today (Monday = day 0)
        $now = Carbon::create(2025, 6, 16, 10, 0, 0, 'Europe/Berlin'); // Monday
        Carbon::setTestNow($now);

        $dayOfWeek = $now->dayOfWeek === 0 ? 6 : $now->dayOfWeek - 1;

        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'open_time' => '12:00',
            'close_time' => '22:00',
        ]);

        // Block today with booking profile
        BlockedDate::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'date' => $now->toDateString(),
            'is_all_day' => true,
            'reason' => 'Private Event',
        ]);

        // Try to access booking page for today
        $response = $this->post('/book/test-restaurant', [
            'date' => $now->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);
        // Should see "no availability" message because day is blocked
        $response->assertSee(__('booking.step_2.no_slots_title'));

        Carbon::setTestNow();
    }

    public function test_opening_hours_with_booking_profile_appear_in_availability(): void
    {
        $now = Carbon::create(2025, 6, 16, 10, 0, 0, 'Europe/Berlin'); // Monday
        Carbon::setTestNow($now);

        $dayOfWeek = $now->dayOfWeek === 0 ? 6 : $now->dayOfWeek - 1;

        // Create opening hours with booking profile
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'open_time' => '12:00',
            'close_time' => '22:00',
        ]);

        // Create a table
        \App\Models\Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Access booking page
        $response = $this->post('/book/test-restaurant', [
            'date' => $now->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);
        // Should see time slots
        $response->assertSee('12:00');

        Carbon::setTestNow();
    }

    public function test_closed_day_shows_no_slots_based_on_booking_hours(): void
    {
        $now = Carbon::create(2025, 6, 16, 10, 0, 0, 'Europe/Berlin'); // Monday
        Carbon::setTestNow($now);

        $dayOfWeek = $now->dayOfWeek === 0 ? 6 : $now->dayOfWeek - 1;

        // Create opening hours with is_open = false
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => $dayOfWeek,
            'is_open' => false,
            'open_time' => '12:00',
            'close_time' => '22:00',
        ]);

        // Access booking page
        $response = $this->post('/book/test-restaurant', [
            'date' => $now->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);
        // Should see "no availability" because day is closed
        $response->assertSee(__('booking.step_2.no_slots_title'));

        Carbon::setTestNow();
    }

    public function test_blocked_date_takes_precedence_over_weekly_hours(): void
    {
        $now = Carbon::create(2025, 6, 16, 10, 0, 0, 'Europe/Berlin'); // Monday
        Carbon::setTestNow($now);

        $dayOfWeek = $now->dayOfWeek === 0 ? 6 : $now->dayOfWeek - 1;

        // Create opening hours (open)
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'open_time' => '12:00',
            'close_time' => '22:00',
        ]);

        // Block today
        BlockedDate::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'date' => $now->toDateString(),
            'is_all_day' => true,
            'reason' => 'Renovation',
        ]);

        // Access booking page
        $response = $this->post('/book/test-restaurant', [
            'date' => $now->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);
        // Blocked date should override weekly hours
        $response->assertSee(__('booking.step_2.no_slots_title'));

        Carbon::setTestNow();
    }

    public function test_profile_filtering_only_uses_booking_hours(): void
    {
        $now = Carbon::create(2025, 6, 16, 10, 0, 0, 'Europe/Berlin'); // Monday
        Carbon::setTestNow($now);

        $dayOfWeek = $now->dayOfWeek === 0 ? 6 : $now->dayOfWeek - 1;

        // Create booking hours (open)
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'booking',
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'open_time' => '18:00',
            'close_time' => '23:00',
        ]);

        // Create "kitchen" hours (different profile - should be ignored)
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'profile' => 'kitchen',
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'open_time' => '11:00',
            'close_time' => '15:00',
        ]);

        // Create a table
        \App\Models\Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Access booking page
        $response = $this->post('/book/test-restaurant', [
            'date' => $now->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);
        // Should only see booking hours (18:00), not kitchen hours (11:00)
        $response->assertSee('18:00');
        $response->assertDontSee('11:00');

        Carbon::setTestNow();
    }
}
