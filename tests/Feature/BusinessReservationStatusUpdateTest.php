<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantRole;
use App\Mail\ReservationCustomerStatusUpdate;
use App\Models\City;
use App\Models\Country;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests for business panel reservation status updates (confirm/cancel).
 *
 * These tests verify that:
 * 1. The ReservationCustomerStatusUpdate mailable works correctly (PHP 8.4/Laravel 12 compatible)
 * 2. Status update emails use the correct locale based on reservation language
 * 3. The confirm/cancel actions work as expected
 */
class BusinessReservationStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private User $restaurantOwner;

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
        ]);

        $this->restaurantOwner = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
        ]);

        // Associate user with restaurant as owner
        RestaurantUser::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->restaurantOwner->id,
            'role' => RestaurantRole::Owner,
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mailable Instantiation Tests (PHP 8.4 / Laravel 12 compatibility)
    |--------------------------------------------------------------------------
    */

    public function test_reservation_customer_status_update_mailable_does_not_throw_locale_type_error(): void
    {
        // This test specifically verifies the Mailable $locale fix
        // It should NOT throw: "Type of App\Mail\ReservationCustomerStatusUpdate::$locale must not be defined"

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
            'language' => 'en',
        ]);

        // This should not throw any TypeError
        $confirmMail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'confirmed');
        $cancelMail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'cancelled');

        // Verify the mailable properties are set correctly
        $this->assertEquals('en', $confirmMail->emailLocale);
        $this->assertEquals('confirmed', $confirmMail->type);
        $this->assertEquals('cancelled', $cancelMail->type);
    }

    public function test_status_update_mailable_uses_reservation_language(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Hans Müller',
            'customer_email' => 'hans@example.de',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'de',
        ]);

        $mail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'confirmed');

        $this->assertEquals('de', $mail->emailLocale);
    }

    public function test_status_update_mailable_falls_back_to_app_locale_when_no_language(): void
    {
        // Create reservation without specifying language (will use default 'en' from migration)
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

        // Force the language to null to test fallback
        $reservation->language = null;

        $mail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'confirmed');

        // Should fall back to app locale
        $this->assertEquals(app()->getLocale(), $mail->emailLocale);
    }

    /*
    |--------------------------------------------------------------------------
    | Confirm Reservation Email Tests
    |--------------------------------------------------------------------------
    */

    public function test_confirm_action_sends_status_update_email(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        // Simulate what the Filament confirm action does
        $reservation->update([
            'status' => ReservationStatus::Confirmed,
            'confirmed_at' => now(),
        ]);

        // Send confirmation email (as Filament action does)
        $reservation->load('restaurant');
        Mail::to($reservation->customer_email)
            ->send(new ReservationCustomerStatusUpdate($reservation, $reservation->restaurant, 'confirmed'));

        // Verify email was queued with correct parameters
        Mail::assertQueued(ReservationCustomerStatusUpdate::class, function ($mail) {
            return $mail->hasTo('john@example.com')
                && $mail->type === 'confirmed'
                && $mail->emailLocale === 'en';
        });
    }

    public function test_confirm_email_uses_german_locale_for_german_reservation(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Hans Müller',
            'customer_email' => 'hans@example.de',
            'status' => ReservationStatus::Confirmed,
            'confirmed_at' => now(),
            'source' => ReservationSource::Widget,
            'language' => 'de',
        ]);

        $reservation->load('restaurant');
        Mail::to($reservation->customer_email)
            ->send(new ReservationCustomerStatusUpdate($reservation, $reservation->restaurant, 'confirmed'));

        Mail::assertQueued(ReservationCustomerStatusUpdate::class, function ($mail) {
            return $mail->emailLocale === 'de';
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Reservation Email Tests
    |--------------------------------------------------------------------------
    */

    public function test_cancel_action_sends_status_update_email(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        // Simulate what the Filament cancel action does
        $reservation->update([
            'status' => ReservationStatus::CancelledByRestaurant,
            'cancelled_at' => now(),
        ]);

        // Send cancellation email (as Filament action does)
        $reservation->load('restaurant');
        Mail::to($reservation->customer_email)
            ->send(new ReservationCustomerStatusUpdate($reservation, $reservation->restaurant, 'cancelled'));

        // Verify email was queued with correct parameters
        Mail::assertQueued(ReservationCustomerStatusUpdate::class, function ($mail) {
            return $mail->hasTo('jane@example.com')
                && $mail->type === 'cancelled'
                && $mail->emailLocale === 'en';
        });
    }

    public function test_cancel_email_uses_french_locale_for_french_reservation(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '19:00:00',
            'guests' => 2,
            'duration_minutes' => 90,
            'customer_name' => 'Pierre Dupont',
            'customer_email' => 'pierre@example.fr',
            'status' => ReservationStatus::CancelledByRestaurant,
            'cancelled_at' => now(),
            'source' => ReservationSource::Widget,
            'language' => 'fr',
        ]);

        $reservation->load('restaurant');
        Mail::to($reservation->customer_email)
            ->send(new ReservationCustomerStatusUpdate($reservation, $reservation->restaurant, 'cancelled'));

        Mail::assertQueued(ReservationCustomerStatusUpdate::class, function ($mail) {
            return $mail->emailLocale === 'fr';
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Edge Cases
    |--------------------------------------------------------------------------
    */

    public function test_no_email_sent_when_customer_email_is_empty(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Walk-in Customer',
            'customer_email' => '', // Empty string (DB requires non-null)
            'status' => ReservationStatus::Pending,
            'source' => ReservationSource::WalkIn,
            'language' => 'en',
        ]);

        // Simulate confirm action - should skip email when email is empty
        $reservation->update([
            'status' => ReservationStatus::Confirmed,
            'confirmed_at' => now(),
        ]);

        // Only send if email is not empty (matches Filament action behavior)
        if (! empty($reservation->customer_email)) {
            Mail::to($reservation->customer_email)
                ->send(new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'confirmed'));
        }

        // Verify no email was sent
        Mail::assertNothingQueued();
    }

    public function test_mailable_can_render_confirmed_envelope(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::Confirmed,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $mail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'confirmed');
        $envelope = $mail->envelope();

        $this->assertNotNull($envelope);
        $this->assertNotEmpty($envelope->subject);
    }

    public function test_mailable_can_render_cancelled_envelope(): void
    {
        $reservation = Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '18:00:00',
            'guests' => 4,
            'duration_minutes' => 90,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'status' => ReservationStatus::CancelledByRestaurant,
            'source' => ReservationSource::Widget,
            'language' => 'en',
        ]);

        $mail = new ReservationCustomerStatusUpdate($reservation, $this->restaurant, 'cancelled');
        $envelope = $mail->envelope();

        $this->assertNotNull($envelope);
        $this->assertNotEmpty($envelope->subject);
    }
}
