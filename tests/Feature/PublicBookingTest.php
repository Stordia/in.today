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

    public function test_booking_page_loads_with_get_no_query_params(): void
    {
        // Test that the initial GET load works without any query parameters
        $response = $this->get('/book/test-restaurant');

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
    }

    public function test_booking_page_accepts_post_with_date_and_party_size(): void
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

        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Test POST request (simulating "Check availability" form submission)
        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
            'party_size' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
        // Verify no query params in the rendered page
        $response->assertDontSee('?date=');
        $response->assertDontSee('&party_size=');
    }

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

        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
        ]);

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

        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
            'party_size' => 4,
        ]);

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

        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
        ]);

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
        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
            'party_size' => 6,
        ]);

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
        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
            'party_size' => 4,
        ]);

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

    /*
    |--------------------------------------------------------------------------
    | Lead Time Tests
    |--------------------------------------------------------------------------
    */

    public function test_today_slots_respect_minimum_lead_time(): void
    {
        // Set "now" to 14:00 in Europe/Berlin
        $now = Carbon::create(2025, 6, 15, 14, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Update restaurant with 60-minute lead time
        $this->restaurant->update([
            'booking_min_lead_time_minutes' => 60,
            'timezone' => 'Europe/Berlin',
        ]);

        // Create opening hours for today (Sunday = 6 in our format)
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        $response = $this->post('/book/test-restaurant', [
            'date' => $today->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);

        // At 14:00 with 60 min lead time, earliest slot should be 15:00
        // Slots 12:00, 12:30, 13:00, 13:30, 14:00, 14:30 should NOT be visible
        $response->assertDontSee('value="12:00"');
        $response->assertDontSee('value="12:30"');
        $response->assertDontSee('value="13:00"');
        $response->assertDontSee('value="13:30"');
        $response->assertDontSee('value="14:00"');
        $response->assertDontSee('value="14:30"');

        // But 15:00 and later should be available
        $response->assertSee('value="15:00"', false);
        $response->assertSee('value="15:30"', false);

        Carbon::setTestNow(); // Reset
    }

    public function test_today_shows_no_slots_when_lead_time_excludes_all(): void
    {
        // Set "now" to 21:30 in Europe/Berlin (close to closing time)
        $now = Carbon::create(2025, 6, 15, 21, 30, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Update restaurant with 60-minute lead time
        $this->restaurant->update([
            'booking_min_lead_time_minutes' => 60,
            'timezone' => 'Europe/Berlin',
        ]);

        // Create opening hours for today - closes at 22:00
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        $response = $this->post('/book/test-restaurant', [
            'date' => $today->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);

        // At 21:30 with 60 min lead time, cutoff is 22:30 - but restaurant closes at 22:00
        // So no slots should be available
        $response->assertSee(__('booking.step_2.no_slots_title'));
        $response->assertDontSee('name="time"');

        Carbon::setTestNow(); // Reset
    }

    public function test_future_date_shows_all_slots_regardless_of_current_time(): void
    {
        // Set "now" to 21:00 in Europe/Berlin (evening)
        $now = Carbon::create(2025, 6, 15, 21, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Update restaurant with 60-minute lead time
        $this->restaurant->update([
            'booking_min_lead_time_minutes' => 60,
            'timezone' => 'Europe/Berlin',
        ]);

        // Create opening hours for tomorrow
        $tomorrow = $now->copy()->addDay();
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
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        $response = $this->post('/book/test-restaurant', [
            'date' => $tomorrow->toDateString(),
            'party_size' => 2,
        ]);

        $response->assertStatus(200);

        // For tomorrow, all slots should be visible from opening time
        // regardless of current time or lead time
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="12:30"', false);
        $response->assertSee('value="13:00"', false);

        Carbon::setTestNow(); // Reset
    }

    /*
    |--------------------------------------------------------------------------
    | Booking Submission & Email Tests
    |--------------------------------------------------------------------------
    */

    public function test_booking_submission_sends_confirmation_emails(): void
    {
        // Mail is already faked in setUp()

        // Set a fixed time for consistent testing
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours for today
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Ensure email sending is enabled in settings
        \App\Services\AppSettings::set('booking.send_customer_confirmation', true);

        // Submit a booking request from the booking page
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 2,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+49123456789',
                'notes' => 'Window seat preferred',
                'accepted_terms' => '1',
            ]);

        // Should redirect back with success
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        // Verify reservation was created
        $this->assertDatabaseHas('reservations', [
            'restaurant_id' => $this->restaurant->id,
            'customer_email' => 'john@example.com',
            'customer_name' => 'John Doe',
            'guests' => 2,
        ]);

        // Verify emails were queued (Mailables implement ShouldQueue)
        Mail::assertQueued(\App\Mail\ReservationCustomerConfirmation::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });

        Carbon::setTestNow(); // Reset
    }

    public function test_booking_submission_redirects_without_query_params(): void
    {
        // Set a fixed time for consistent testing
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours for today
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Submit a booking request
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+49123456789',
                'accepted_terms' => '1',
            ]);

        // Should redirect to booking page WITHOUT query parameters
        $response->assertRedirect('/book/test-restaurant');
        $response->assertSessionHas('booking_status', 'success');

        // Follow the redirect and verify success message appears
        $followResponse = $this->get($response->headers->get('Location'));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('Test Restaurant');

        Carbon::setTestNow(); // Reset
    }

    public function test_booking_submission_without_fatal_error(): void
    {
        // This test specifically verifies the Mailable $locale fix
        // It should NOT throw: "Type of App\Mail\...::$locale must not be defined"

        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Ensure email sending is enabled in settings
        \App\Services\AppSettings::set('booking.send_customer_confirmation', true);

        // Submit booking from the booking page - this should not throw any fatal error
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'accepted_terms' => '1',
            ]);

        // If we get here without exception, the $locale fix is working
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        // Both mailable classes should be instantiable without error (queued, not sent directly)
        Mail::assertQueued(\App\Mail\ReservationCustomerConfirmation::class);

        Carbon::setTestNow();
    }

    /*
    |--------------------------------------------------------------------------
    | Edge Cases: Max Party Size Validation
    |--------------------------------------------------------------------------
    */

    public function test_party_size_above_max_shows_friendly_error(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 20,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Restaurant max party size is 10 (set in setUp)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 15, // Above max of 10
                'name' => 'Large Group',
                'email' => 'large@example.com',
                'accepted_terms' => '1',
            ]);

        // Should redirect back with validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors('party_size');

        // Verify the error contains the friendly message (not a generic "must be at most 10")
        $errors = session('errors');
        $partyError = $errors->get('party_size')[0] ?? '';
        $this->assertStringContainsString('10', $partyError);

        Carbon::setTestNow();
    }

    public function test_party_size_within_range_passes_validation(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 10,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Party size of 8 is within min (2) and max (10)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 8,
                'name' => 'Valid Group',
                'email' => 'valid@example.com',
                'accepted_terms' => '1',
            ]);

        // Should succeed
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        Carbon::setTestNow();
    }

    public function test_party_size_below_min_shows_error(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 10,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Restaurant min party size is 2 (set in setUp)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 1, // Below min of 2
                'name' => 'Solo Diner',
                'email' => 'solo@example.com',
                'accepted_terms' => '1',
            ]);

        // Should redirect back with validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors('party_size');

        Carbon::setTestNow();
    }

    /*
    |--------------------------------------------------------------------------
    | Edge Cases: Capacity / Overbooking Prevention
    |--------------------------------------------------------------------------
    */

    public function test_fully_booked_slot_not_available_for_booking(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a small table (4 seats only)
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => false,
        ]);

        // Create an existing reservation that fills the capacity at 12:00
        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $today->toDateString(),
            'time' => '12:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Existing Guest',
            'customer_email' => 'existing@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
        ]);

        // Try to book a party of 2 at the same time (should fail since capacity is full)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 2,
                'name' => 'New Guest',
                'email' => 'new@example.com',
                'accepted_terms' => '1',
            ]);

        // Should fail with slot unavailable error
        $response->assertRedirect();
        $response->assertSessionHasErrors('time');

        // Verify no new reservation was created
        $this->assertDatabaseCount('reservations', 1);

        Carbon::setTestNow();
    }

    public function test_partial_capacity_allows_smaller_party(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a larger table (10 seats)
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 10,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Create an existing reservation for 4 guests at 12:00
        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $today->toDateString(),
            'time' => '12:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Existing Guest',
            'customer_email' => 'existing@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
        ]);

        // Book a party of 4 at the same time (should work, 10 - 4 = 6 available)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 4,
                'name' => 'New Guest',
                'email' => 'new@example.com',
                'accepted_terms' => '1',
            ]);

        // Should succeed
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        // Verify new reservation was created
        $this->assertDatabaseCount('reservations', 2);

        Carbon::setTestNow();
    }

    public function test_cancelled_reservations_do_not_block_capacity(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a small table (4 seats)
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => false,
        ]);

        // Create a CANCELLED reservation
        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $today->toDateString(),
            'time' => '12:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Cancelled Guest',
            'customer_email' => 'cancelled@example.com',
            'status' => ReservationStatus::CancelledByCustomer,
            'source' => ReservationSource::Widget,
        ]);

        // Book a new party at the same time (should work since previous is cancelled)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 4,
                'name' => 'New Guest',
                'email' => 'new@example.com',
                'accepted_terms' => '1',
            ]);

        // Should succeed
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        Carbon::setTestNow();
    }

    public function test_slot_filled_after_page_load_shows_error(): void
    {
        // This test simulates the race condition where a slot is booked
        // after the user loads the page but before they submit

        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a small table (4 seats)
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 4,
            'is_active' => true,
            'is_combinable' => false,
        ]);

        // First, verify the slot is available
        $response = $this->post('/book/test-restaurant', [
            'date' => $today->toDateString(),
            'party_size' => 4,
        ]);
        $response->assertStatus(200);
        $response->assertSee('value="12:00"', false);

        // Simulate another user booking the slot while first user is filling form
        Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $today->toDateString(),
            'time' => '12:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Fast Booker',
            'customer_email' => 'fast@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
        ]);

        // Now first user tries to submit (should fail)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 4,
                'name' => 'Slow Booker',
                'email' => 'slow@example.com',
                'accepted_terms' => '1',
            ]);

        // Should fail - the controller re-validates availability
        $response->assertRedirect();
        $response->assertSessionHasErrors('time');

        Carbon::setTestNow();
    }

    /*
    |--------------------------------------------------------------------------
    | Edge Cases: Email Failure Handling
    |--------------------------------------------------------------------------
    */

    public function test_booking_succeeds_even_when_email_fails(): void
    {
        // Set a fixed time
        $now = Carbon::create(2025, 6, 15, 10, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Create opening hours
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Configure mail to throw an exception
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail server unavailable'));

        // Enable email sending
        \App\Services\AppSettings::set('booking.send_customer_confirmation', true);

        // Submit booking
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '12:00',
                'party_size' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'accepted_terms' => '1',
            ]);

        // Should still succeed - email failure is caught
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        // Verify reservation was created despite email failure
        $this->assertDatabaseHas('reservations', [
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
        ]);

        Carbon::setTestNow();
    }

    /*
    |--------------------------------------------------------------------------
    | Edge Cases: Lead Time for POST submission
    |--------------------------------------------------------------------------
    */

    public function test_booking_slot_too_soon_is_rejected(): void
    {
        // Set "now" to 14:00 - with 60 min lead time, only 15:00+ should be bookable
        $now = Carbon::create(2025, 6, 15, 14, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Update restaurant with 60-minute lead time
        $this->restaurant->update([
            'booking_min_lead_time_minutes' => 60,
            'timezone' => 'Europe/Berlin',
        ]);

        // Create opening hours for today
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Try to book 14:30 (which is before the 15:00 cutoff)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '14:30',
                'party_size' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'accepted_terms' => '1',
            ]);

        // Should fail - slot is too soon
        $response->assertRedirect();
        $response->assertSessionHasErrors('time');

        Carbon::setTestNow();
    }

    public function test_booking_slot_after_lead_time_succeeds(): void
    {
        // Set "now" to 14:00 - with 60 min lead time, 15:00+ should be bookable
        $now = Carbon::create(2025, 6, 15, 14, 0, 0, 'Europe/Berlin');
        Carbon::setTestNow($now);

        // Update restaurant with 60-minute lead time
        $this->restaurant->update([
            'booking_min_lead_time_minutes' => 60,
            'timezone' => 'Europe/Berlin',
        ]);

        // Create opening hours for today
        $today = $now->copy();
        OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $today->dayOfWeek === 0 ? 6 : $today->dayOfWeek - 1,
            'open_time' => '12:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        // Create a table
        Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table 1',
            'seats' => 6,
            'is_active' => true,
            'is_combinable' => true,
        ]);

        // Book 15:00 (which is after the cutoff)
        $response = $this->from('/book/test-restaurant')
            ->post('/book/test-restaurant/request', [
                'date' => $today->toDateString(),
                'time' => '15:00',
                'party_size' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'accepted_terms' => '1',
            ]);

        // Should succeed
        $response->assertRedirect();
        $response->assertSessionHas('booking_status', 'success');

        Carbon::setTestNow();
    }
}
