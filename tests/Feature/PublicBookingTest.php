<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DepositStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Models\City;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\Restaurant;
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
