<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DepositStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Models\City;
use App\Models\Country;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private Country $country;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

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
            'booking_public_slug' => 'test-restaurant',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 10,
            'booking_default_duration_minutes' => 90,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
            // Deposit settings
            'booking_deposit_enabled' => false,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 25.00,
            'booking_deposit_currency' => 'EUR',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Booking Page Tests
    |--------------------------------------------------------------------------
    */

    public function test_booking_page_returns_404_for_invalid_slug(): void
    {
        $response = $this->get('/book/non-existent-restaurant');

        $response->assertStatus(404);
    }

    public function test_booking_page_returns_404_for_disabled_booking(): void
    {
        $this->restaurant->update(['booking_enabled' => false]);

        $response = $this->get('/book/test-restaurant');

        $response->assertStatus(404);
    }

    public function test_booking_page_loads_with_valid_restaurant(): void
    {
        // Create opening hours for tomorrow
        $tomorrow = Carbon::now('Europe/Berlin')->addDay();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $tomorrow->dayOfWeek === 0 ? 6 : $tomorrow->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        $response = $this->get('/book/test-restaurant?date=' . $tomorrow->toDateString());

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
        $response->assertSee('type="date"', false); // HTML5 date input (false = don't escape)
    }

    public function test_booking_page_shows_time_slots_when_available(): void
    {
        // Create opening hours for tomorrow
        $tomorrow = Carbon::now('Europe/Berlin')->addDay();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $tomorrow->dayOfWeek === 0 ? 6 : $tomorrow->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table with capacity
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        $response = $this->get('/book/test-restaurant?date=' . $tomorrow->toDateString() . '&party_size=4');

        $response->assertStatus(200);
        // Should see time slot radio buttons (12:00, 12:30, etc.)
        $response->assertSee('12:00');
        $response->assertSee('12:30');
        // Should see time radio inputs for slot selection
        $response->assertSee('name="time"', false);
        $response->assertSee('type="radio"', false);
        // Should see the booking form section (step 3) - check for id attributes instead
        $response->assertSee('id="name"', false);
        $response->assertSee('id="email"', false);
    }

    public function test_booking_page_shows_no_slots_message_when_closed(): void
    {
        // Don't create any opening hours - restaurant is closed
        $tomorrow = Carbon::now('Europe/Berlin')->addDay();

        $response = $this->get('/book/test-restaurant?date=' . $tomorrow->toDateString());

        $response->assertStatus(200);
        // Should see the "no availability" message
        $response->assertSee(__('booking.step_2.no_slots_title'));
        // Should NOT see any time radio inputs
        $response->assertDontSee('name="time"');
    }

    public function test_booking_page_shows_deposit_info_for_large_party(): void
    {
        // Enable deposits
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 25.00,
            'booking_deposit_currency' => 'EUR',
        ]);

        // Create opening hours for tomorrow
        $tomorrow = Carbon::now('Europe/Berlin')->addDay();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $tomorrow->dayOfWeek === 0 ? 6 : $tomorrow->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create tables with enough capacity
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 10,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Request with party size above threshold
        $response = $this->get('/book/test-restaurant?date=' . $tomorrow->toDateString() . '&party_size=6');

        $response->assertStatus(200);
        // Should see deposit info
        $response->assertSee(__('booking.deposit.title'));
        $response->assertSee('accepted_deposit'); // Consent checkbox
    }

    public function test_booking_page_hides_deposit_info_for_small_party(): void
    {
        // Enable deposits with threshold of 6
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 25.00,
        ]);

        // Create opening hours for tomorrow
        $tomorrow = Carbon::now('Europe/Berlin')->addDay();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $tomorrow->dayOfWeek === 0 ? 6 : $tomorrow->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 10,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Request with party size below threshold
        $response = $this->get('/book/test-restaurant?date=' . $tomorrow->toDateString() . '&party_size=4');

        $response->assertStatus(200);
        // Should NOT see the deposit consent checkbox (look for the specific input name)
        $response->assertDontSee('name="accepted_deposit"');
    }

    /*
    |--------------------------------------------------------------------------
    | Deposit Logic Tests (Restaurant Model)
    |--------------------------------------------------------------------------
    */

    public function test_deposit_not_required_when_disabled(): void
    {
        $this->restaurant->update(['booking_deposit_enabled' => false]);

        $this->assertFalse($this->restaurant->requiresDeposit(6));
        $this->assertFalse($this->restaurant->requiresDeposit(10));
    }

    public function test_deposit_required_when_party_size_meets_threshold(): void
    {
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
        ]);

        $this->assertFalse($this->restaurant->requiresDeposit(5));
        $this->assertTrue($this->restaurant->requiresDeposit(6));
        $this->assertTrue($this->restaurant->requiresDeposit(10));
    }

    public function test_deposit_amount_calculation_fixed_per_person(): void
    {
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 25.00,
        ]);

        // 6 guests * 25 EUR = 150 EUR
        $this->assertEquals(150.00, $this->restaurant->calculateDepositAmount(6));

        // 10 guests * 25 EUR = 250 EUR
        $this->assertEquals(250.00, $this->restaurant->calculateDepositAmount(10));
    }

    public function test_deposit_amount_calculation_flat_rate(): void
    {
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'flat_rate',
            'booking_deposit_amount' => 100.00,
        ]);

        // Flat rate regardless of party size
        $this->assertEquals(100.00, $this->restaurant->calculateDepositAmount(6));
        $this->assertEquals(100.00, $this->restaurant->calculateDepositAmount(10));
    }

    public function test_deposit_amount_returns_zero_when_not_required(): void
    {
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
        ]);

        // Below threshold - no deposit required
        $this->assertEquals(0.0, $this->restaurant->calculateDepositAmount(4));
    }

    public function test_formatted_deposit_amount(): void
    {
        $this->restaurant->update([
            'booking_deposit_enabled' => true,
            'booking_deposit_threshold_party_size' => 6,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 25.00,
            'booking_deposit_currency' => 'EUR',
        ]);

        // 6 * 25 = 150 EUR
        $formatted = $this->restaurant->getFormattedDepositAmount(6);
        $this->assertStringContainsString('150', $formatted);
        $this->assertStringContainsString('EUR', $formatted);
    }

    /*
    |--------------------------------------------------------------------------
    | Deposit Helper Method Tests (Reservation Model)
    |--------------------------------------------------------------------------
    */

    public function test_reservation_deposit_helpers(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 6,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'deposit_required' => true,
            'deposit_amount' => 150.00,
            'deposit_currency' => 'EUR',
            'deposit_status' => DepositStatus::Pending,
        ]);

        $this->assertTrue($reservation->hasDepositRequired());
        $this->assertTrue($reservation->isDepositPending());
        $this->assertFalse($reservation->isDepositPaid());
        $this->assertFalse($reservation->isDepositWaived());
        $this->assertEquals('150,00 EUR', $reservation->getFormattedDepositAmount());
    }

    public function test_reservation_without_deposit(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'deposit_required' => false,
            'deposit_status' => DepositStatus::None,
        ]);

        $this->assertFalse($reservation->hasDepositRequired());
        $this->assertFalse($reservation->isDepositPending());
        $this->assertNull($reservation->getFormattedDepositAmount());
    }

    public function test_reservation_mark_deposit_paid(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 6,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'deposit_required' => true,
            'deposit_amount' => 150.00,
            'deposit_currency' => 'EUR',
            'deposit_status' => DepositStatus::Pending,
        ]);

        $reservation->markDepositPaid('Received via bank transfer');
        $reservation->refresh();

        $this->assertTrue($reservation->isDepositPaid());
        $this->assertEquals(DepositStatus::Paid, $reservation->deposit_status);
        $this->assertEquals('Received via bank transfer', $reservation->deposit_notes);
    }

    public function test_reservation_waive_deposit(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 6,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'deposit_required' => true,
            'deposit_amount' => 150.00,
            'deposit_currency' => 'EUR',
            'deposit_status' => DepositStatus::Pending,
        ]);

        $reservation->waiveDeposit('Regular customer');
        $reservation->refresh();

        $this->assertTrue($reservation->isDepositWaived());
        $this->assertEquals(DepositStatus::Waived, $reservation->deposit_status);
        $this->assertEquals('Regular customer', $reservation->deposit_notes);
    }

    public function test_reservation_has_uuid(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
        ]);

        $this->assertNotNull($reservation->uuid);
        $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $reservation->uuid);
    }

    /*
    |--------------------------------------------------------------------------
    | DepositStatus Enum Tests
    |--------------------------------------------------------------------------
    */

    public function test_deposit_status_labels(): void
    {
        $this->assertEquals('No Deposit', DepositStatus::None->label());
        $this->assertEquals('Pending', DepositStatus::Pending->label());
        $this->assertEquals('Paid', DepositStatus::Paid->label());
        $this->assertEquals('Waived', DepositStatus::Waived->label());
    }

    public function test_deposit_status_helpers(): void
    {
        $this->assertTrue(DepositStatus::Pending->isPending());
        $this->assertFalse(DepositStatus::Paid->isPending());

        $this->assertTrue(DepositStatus::Paid->isPaid());
        $this->assertFalse(DepositStatus::Pending->isPaid());

        $this->assertTrue(DepositStatus::Pending->requiresAction());
        $this->assertFalse(DepositStatus::Paid->requiresAction());
        $this->assertFalse(DepositStatus::Waived->requiresAction());
    }
}
