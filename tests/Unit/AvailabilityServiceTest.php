<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Models\BlockedDate;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Services\Reservations\AvailabilityResult;
use App\Services\Reservations\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $service;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AvailabilityService;

        // Create a test restaurant
        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'Europe/Berlin',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create opening hours for a specific day.
     */
    private function createOpeningHours(
        int $dayOfWeek,
        string $openTime,
        string $closeTime,
        ?string $lastReservationTime = null,
    ): OpeningHour {
        return OpeningHour::create([
            'restaurant_id' => $this->restaurant->id,
            'day_of_week' => $dayOfWeek,
            'is_open' => true,
            'shift_name' => 'Dinner',
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'last_reservation_time' => $lastReservationTime,
        ]);
    }

    /**
     * Helper to create a table.
     */
    private function createTable(
        int $seats,
        int $minGuests = 1,
        ?int $maxGuests = null,
        bool $isCombinable = true,
        bool $isActive = true,
    ): Table {
        return Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Table ' . rand(1, 1000),
            'seats' => $seats,
            'min_guests' => $minGuests,
            'max_guests' => $maxGuests ?? $seats,
            'is_combinable' => $isCombinable,
            'is_active' => $isActive,
        ]);
    }

    /**
     * Helper to create a reservation.
     */
    private function createReservation(
        string $date,
        string $time,
        int $guests,
        ReservationStatus $status = ReservationStatus::Confirmed,
        ?int $tableId = null,
        int $durationMinutes = 90,
    ): Reservation {
        return Reservation::create([
            'restaurant_id' => $this->restaurant->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
            'duration_minutes' => $durationMinutes,
            'table_id' => $tableId,
            'status' => $status,
            'source' => ReservationSource::Widget,
        ]);
    }

    /**
     * Helper to create a blocked date.
     */
    private function createBlockedDate(
        string $date,
        bool $isAllDay = true,
        ?string $timeFrom = null,
        ?string $timeTo = null,
    ): BlockedDate {
        return BlockedDate::create([
            'restaurant_id' => $this->restaurant->id,
            'date' => $date,
            'is_all_day' => $isAllDay,
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'reason' => 'Test block',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Simple open day, no blocks, no reservations
    |--------------------------------------------------------------------------
    */

    public function test_simple_open_day_returns_all_slots_as_bookable(): void
    {
        // Monday = day_of_week 0
        // Create opening hours 18:00-22:00
        $this->createOpeningHours(0, '18:00', '22:00');

        // Create 4 tables with 4 seats each = 16 total capacity
        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4, minGuests: 1, maxGuests: 4);
        }

        // Query for a Monday
        $date = Carbon::parse('2025-12-01'); // This is a Monday

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        // Should have slots from 18:00 to 21:30 (8 slots at 30-min intervals)
        $this->assertInstanceOf(AvailabilityResult::class, $result);
        $this->assertTrue($result->hasAnySlots());
        $this->assertTrue($result->hasBookableSlots());

        // 18:00, 18:30, 19:00, 19:30, 20:00, 20:30, 21:00, 21:30 = 8 slots
        $this->assertCount(8, $result->slots);

        // All slots should be bookable
        $this->assertCount(8, $result->onlyBookable());

        // Check first slot
        $firstSlot = $result->findSlotByTime('18:00');
        $this->assertNotNull($firstSlot);
        $this->assertTrue($firstSlot->isBookable);
        $this->assertEquals(16, $firstSlot->maxPartySizeForSlot);

        // Check last slot
        $lastSlot = $result->findSlotByTime('21:30');
        $this->assertNotNull($lastSlot);
        $this->assertTrue($lastSlot->isBookable);
    }

    public function test_party_size_4_fits_in_capacity(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4, minGuests: 1, maxGuests: 4);
        }

        $date = Carbon::parse('2025-12-01'); // Monday

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 4,
        );

        $this->assertTrue($result->hasBookableSlots());
        $slot = $result->findSlotByTime('18:00');
        $this->assertTrue($slot->isBookable);
        $this->assertEquals(16, $slot->maxPartySizeForSlot);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Fully blocked day
    |--------------------------------------------------------------------------
    */

    public function test_fully_blocked_day_returns_no_slots(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Block the entire day
        $this->createBlockedDate('2025-12-01', isAllDay: true);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        $this->assertFalse($result->hasAnySlots());
        $this->assertCount(0, $result->slots);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Partially blocked evening
    |--------------------------------------------------------------------------
    */

    public function test_partially_blocked_evening_excludes_blocked_slots(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Block from 20:00 to 21:00
        $this->createBlockedDate(
            '2025-12-01',
            isAllDay: false,
            timeFrom: '20:00',
            timeTo: '21:00'
        );

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        $this->assertTrue($result->hasAnySlots());

        // Slots before 20:00 should exist: 18:00, 18:30, 19:00, 19:30 = 4
        // Slots at 20:00 and 20:30 should be blocked
        // Slots after 21:00: 21:00, 21:30 = 2
        // Total: 6 slots

        $slot1800 = $result->findSlotByTime('18:00');
        $this->assertNotNull($slot1800);
        $this->assertTrue($slot1800->isBookable);

        $slot1930 = $result->findSlotByTime('19:30');
        $this->assertNotNull($slot1930);
        $this->assertTrue($slot1930->isBookable);

        // These slots should not exist (blocked)
        $slot2000 = $result->findSlotByTime('20:00');
        $this->assertNull($slot2000);

        $slot2030 = $result->findSlotByTime('20:30');
        $this->assertNull($slot2030);

        // Slot at 21:00 should exist (after block ends)
        $slot2100 = $result->findSlotByTime('21:00');
        $this->assertNotNull($slot2100);
        $this->assertTrue($slot2100->isBookable);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Reservations consuming capacity
    |--------------------------------------------------------------------------
    */

    public function test_reservations_reduce_available_capacity(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // 4 tables x 4 seats = 16 capacity
        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Create a confirmed reservation for 10 guests at 18:30 (duration 90 min)
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 10,
            status: ReservationStatus::Confirmed,
            durationMinutes: 90,
        );

        $date = Carbon::parse('2025-12-01');

        // Query for party of 8
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 8,
        );

        // Slot at 18:30 should NOT be bookable (16 - 10 = 6, which is < 8)
        $slot1830 = $result->findSlotByTime('18:30');
        $this->assertNotNull($slot1830);
        $this->assertFalse($slot1830->isBookable);
        $this->assertEquals(6, $slot1830->maxPartySizeForSlot);

        // Slot at 19:00 should also be affected (reservation lasts until 20:00)
        $slot1900 = $result->findSlotByTime('19:00');
        $this->assertNotNull($slot1900);
        $this->assertFalse($slot1900->isBookable);
        $this->assertEquals(6, $slot1900->maxPartySizeForSlot);

        // Slot at 20:00 should be bookable (reservation ended)
        $slot2000 = $result->findSlotByTime('20:00');
        $this->assertNotNull($slot2000);
        $this->assertTrue($slot2000->isBookable);
        $this->assertEquals(16, $slot2000->maxPartySizeForSlot);
    }

    public function test_small_party_can_book_during_partial_occupancy(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Reservation for 10 guests
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 10,
        );

        $date = Carbon::parse('2025-12-01');

        // Query for party of 4 (which fits in remaining 6 capacity)
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 4,
        );

        $slot1830 = $result->findSlotByTime('18:30');
        $this->assertNotNull($slot1830);
        $this->assertTrue($slot1830->isBookable);
        $this->assertEquals(6, $slot1830->maxPartySizeForSlot);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Status filtering
    |--------------------------------------------------------------------------
    */

    public function test_cancelled_reservations_do_not_affect_capacity(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Create a cancelled reservation for 10 guests
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 10,
            status: ReservationStatus::CancelledByCustomer,
        );

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 8,
        );

        // Cancelled reservation should not affect capacity
        $slot1830 = $result->findSlotByTime('18:30');
        $this->assertTrue($slot1830->isBookable);
        $this->assertEquals(16, $slot1830->maxPartySizeForSlot);
    }

    public function test_no_show_reservations_do_not_affect_capacity(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Create a no-show reservation
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 10,
            status: ReservationStatus::NoShow,
        );

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 8,
        );

        $slot1830 = $result->findSlotByTime('18:30');
        $this->assertTrue($slot1830->isBookable);
        $this->assertEquals(16, $slot1830->maxPartySizeForSlot);
    }

    public function test_pending_and_completed_reservations_affect_capacity(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        // Pending reservation
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 5,
            status: ReservationStatus::Pending,
        );

        // Completed reservation
        $this->createReservation(
            date: '2025-12-01',
            time: '18:30',
            guests: 5,
            status: ReservationStatus::Completed,
        );

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        // Both reservations should consume capacity (5 + 5 = 10)
        $slot1830 = $result->findSlotByTime('18:30');
        $this->assertEquals(6, $slot1830->maxPartySizeForSlot);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Party size too large
    |--------------------------------------------------------------------------
    */

    public function test_party_size_exceeds_total_capacity_returns_no_slots(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Only 16 seats total
        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        $date = Carbon::parse('2025-12-01');

        // Request for 20 guests
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 20,
        );

        $this->assertFalse($result->hasAnySlots());
        $this->assertCount(0, $result->slots);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: No opening hours for day
    |--------------------------------------------------------------------------
    */

    public function test_no_opening_hours_returns_no_slots(): void
    {
        // No opening hours created for Monday
        for ($i = 0; $i < 4; $i++) {
            $this->createTable(seats: 4);
        }

        $date = Carbon::parse('2025-12-01'); // Monday

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        $this->assertFalse($result->hasAnySlots());
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Table-based capacity and combinability
    |--------------------------------------------------------------------------
    */

    public function test_single_table_can_accommodate_party(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Single table that can fit 6 guests
        $this->createTable(seats: 6, minGuests: 1, maxGuests: 6);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 5,
        );

        $slot = $result->findSlotByTime('18:00');
        $this->assertTrue($slot->isBookable);
    }

    public function test_combined_tables_can_accommodate_larger_party(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Two tables of 4 seats each, both combinable
        $this->createTable(seats: 4, minGuests: 1, maxGuests: 4, isCombinable: true);
        $this->createTable(seats: 4, minGuests: 1, maxGuests: 4, isCombinable: true);

        $date = Carbon::parse('2025-12-01');

        // Party of 6 needs combined tables
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 6,
        );

        $slot = $result->findSlotByTime('18:00');
        $this->assertTrue($slot->isBookable);
    }

    public function test_non_combinable_tables_cannot_accommodate_larger_party(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Two tables of 4 seats each, NOT combinable
        $this->createTable(seats: 4, minGuests: 1, maxGuests: 4, isCombinable: false);
        $this->createTable(seats: 4, minGuests: 1, maxGuests: 4, isCombinable: false);

        $date = Carbon::parse('2025-12-01');

        // Party of 6 needs combined tables but they can't be combined
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 6,
        );

        $slot = $result->findSlotByTime('18:00');
        $this->assertFalse($slot->isBookable);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Occupied tables affect availability
    |--------------------------------------------------------------------------
    */

    public function test_occupied_table_reduces_options(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Two tables of 4 seats each
        $table1 = $this->createTable(seats: 4);
        $table2 = $this->createTable(seats: 4);

        // One table is occupied
        $this->createReservation(
            date: '2025-12-01',
            time: '18:00',
            guests: 4,
            tableId: $table1->id,
        );

        $date = Carbon::parse('2025-12-01');

        // Query for party of 4 (should still fit on table2)
        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 4,
        );

        $slot = $result->findSlotByTime('18:00');
        $this->assertTrue($slot->isBookable);
        $this->assertEquals(4, $slot->maxPartySizeForSlot); // 8 - 4 = 4 remaining capacity
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Last reservation time
    |--------------------------------------------------------------------------
    */

    public function test_respects_last_reservation_time(): void
    {
        // Open 18:00-22:00 but last reservation at 20:00
        $this->createOpeningHours(0, '18:00', '22:00', '20:00');

        $this->createTable(seats: 4);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        // Should only have slots until 20:00 (not inclusive of times after)
        // 18:00, 18:30, 19:00, 19:30 = 4 slots
        $this->assertCount(4, $result->slots);

        $slot1930 = $result->findSlotByTime('19:30');
        $this->assertNotNull($slot1930);

        $slot2000 = $result->findSlotByTime('20:00');
        $this->assertNull($slot2000);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Multiple shifts per day
    |--------------------------------------------------------------------------
    */

    public function test_multiple_shifts_generate_separate_slots(): void
    {
        // Lunch: 12:00-14:30
        $this->createOpeningHours(0, '12:00', '14:30');
        // Dinner: 18:00-22:00
        $this->createOpeningHours(0, '18:00', '22:00');

        $this->createTable(seats: 4);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        // Lunch: 12:00, 12:30, 13:00, 13:30, 14:00 = 5 slots
        // Dinner: 18:00, 18:30, 19:00, 19:30, 20:00, 20:30, 21:00, 21:30 = 8 slots
        // Total = 13 slots
        $this->assertCount(13, $result->slots);

        // Verify lunch slots
        $this->assertNotNull($result->findSlotByTime('12:00'));
        $this->assertNotNull($result->findSlotByTime('14:00'));

        // Verify dinner slots
        $this->assertNotNull($result->findSlotByTime('18:00'));
        $this->assertNotNull($result->findSlotByTime('21:30'));
    }

    /*
    |--------------------------------------------------------------------------
    | Test: Inactive tables are not counted
    |--------------------------------------------------------------------------
    */

    public function test_inactive_tables_are_not_counted(): void
    {
        $this->createOpeningHours(0, '18:00', '22:00');

        // Active table
        $this->createTable(seats: 4, isActive: true);
        // Inactive table
        $this->createTable(seats: 4, isActive: false);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        // Only 4 seats should be counted (not 8)
        $slot = $result->findSlotByTime('18:00');
        $this->assertEquals(4, $slot->maxPartySizeForSlot);
    }

    /*
    |--------------------------------------------------------------------------
    | Test: DTO helper methods
    |--------------------------------------------------------------------------
    */

    public function test_availability_result_helper_methods(): void
    {
        $this->createOpeningHours(0, '18:00', '20:00');

        $this->createTable(seats: 4);

        // Create reservation to make some slots not bookable
        $this->createReservation(
            date: '2025-12-01',
            time: '18:00',
            guests: 4,
        );

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 4,
        );

        $this->assertTrue($result->hasAnySlots());
        $this->assertEquals(4, $result->totalSlots());
        $this->assertEquals($date->toDateString(), $result->date->toDateString());
        $this->assertEquals(4, $result->partySize);
    }

    public function test_time_slot_availability_helper_methods(): void
    {
        $this->createOpeningHours(0, '18:00', '19:00');
        $this->createTable(seats: 4);

        $date = Carbon::parse('2025-12-01');

        $result = $this->service->getAvailableTimeSlots(
            restaurant: $this->restaurant,
            date: $date,
            partySize: 2,
        );

        $slot = $result->findSlotByTime('18:00');

        $this->assertEquals('18:00', $slot->getStartTime());
        $this->assertEquals('18:30', $slot->getEndTime());
    }
}
