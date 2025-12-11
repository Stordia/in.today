<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use App\Enums\ReservationStatus;
use App\Models\BlockedDate;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Central service for computing restaurant availability.
 *
 * This service answers: "For restaurant X, on date Y, for a party size Z,
 * what time slots are available?"
 */
class AvailabilityService
{
    /**
     * Default slot interval in minutes.
     */
    private const int SLOT_INTERVAL_MINUTES = 30;

    /**
     * Default reservation duration in minutes (used for overlap calculations).
     */
    private const int DEFAULT_RESERVATION_DURATION_MINUTES = 90;

    /**
     * Statuses that count as "occupied" for capacity calculations.
     */
    private const array OCCUPIED_STATUSES = [
        ReservationStatus::Pending,
        ReservationStatus::Confirmed,
        ReservationStatus::Completed,
    ];

    /**
     * Get available time slots for a restaurant on a given date for a party size.
     */
    public function getAvailableTimeSlots(
        Restaurant $restaurant,
        CarbonInterface $date,
        int $partySize,
    ): AvailabilityResult {
        // Normalize date to start of day
        $date = $date->copy()->startOfDay();

        // Check if the entire day is blocked
        if ($this->isDayFullyBlocked($restaurant, $date)) {
            return new AvailabilityResult(
                date: $date,
                partySize: $partySize,
                slots: [],
            );
        }

        // Get opening hours for this day of week
        $openingHours = $this->getOpeningHoursForDate($restaurant, $date);

        if ($openingHours->isEmpty()) {
            return new AvailabilityResult(
                date: $date,
                partySize: $partySize,
                slots: [],
            );
        }

        // Get blocked time ranges for this date
        $blockedRanges = $this->getBlockedTimeRanges($restaurant, $date);

        // Get all active tables for capacity calculation
        $tables = $this->getActiveTables($restaurant);
        $totalCapacity = $this->calculateTotalCapacity($tables);

        // If party size exceeds total capacity, no slots are bookable
        if ($partySize > $totalCapacity) {
            return new AvailabilityResult(
                date: $date,
                partySize: $partySize,
                slots: [],
            );
        }

        // Get existing reservations for the date
        $reservations = $this->getReservationsForDate($restaurant, $date);

        // Generate slots from opening hours
        $slots = $this->generateSlots(
            restaurant: $restaurant,
            date: $date,
            openingHours: $openingHours,
            blockedRanges: $blockedRanges,
            tables: $tables,
            reservations: $reservations,
            partySize: $partySize,
            totalCapacity: $totalCapacity,
        );

        return new AvailabilityResult(
            date: $date,
            partySize: $partySize,
            slots: $slots,
        );
    }

    /**
     * Check if the entire day is blocked for bookings.
     */
    private function isDayFullyBlocked(Restaurant $restaurant, CarbonInterface $date): bool
    {
        return BlockedDate::query()
            ->where('restaurant_id', $restaurant->id)
            ->bookingProfile()
            ->whereDate('date', $date->toDateString())
            ->where('is_all_day', true)
            ->exists();
    }

    /**
     * Get booking opening hours for the given date's day of week.
     *
     * @return Collection<OpeningHour>
     */
    private function getOpeningHoursForDate(Restaurant $restaurant, CarbonInterface $date): Collection
    {
        // Carbon dayOfWeek: 0 = Sunday, 1 = Monday, ... 6 = Saturday
        // OpeningHour day_of_week: 0 = Monday, ... 6 = Sunday
        // Convert Carbon's dayOfWeek to our format
        $dayOfWeek = $date->dayOfWeek === 0 ? 6 : $date->dayOfWeek - 1;

        return OpeningHour::query()
            ->where('restaurant_id', $restaurant->id)
            ->bookingProfile()
            ->forDay($dayOfWeek)
            ->open()
            ->get();
    }

    /**
     * Get blocked time ranges for a specific date (booking profile only).
     *
     * @return array<array{from: string, to: string}>
     */
    private function getBlockedTimeRanges(Restaurant $restaurant, CarbonInterface $date): array
    {
        $blockedDates = BlockedDate::query()
            ->where('restaurant_id', $restaurant->id)
            ->bookingProfile()
            ->whereDate('date', $date->toDateString())
            ->where('is_all_day', false)
            ->get();

        $ranges = [];

        foreach ($blockedDates as $blocked) {
            if ($blocked->time_from && $blocked->time_to) {
                $ranges[] = [
                    'from' => $blocked->time_from instanceof CarbonInterface
                        ? $blocked->time_from->format('H:i')
                        : (string) $blocked->time_from,
                    'to' => $blocked->time_to instanceof CarbonInterface
                        ? $blocked->time_to->format('H:i')
                        : (string) $blocked->time_to,
                ];
            }
        }

        return $ranges;
    }

    /**
     * Get all active tables for a restaurant.
     *
     * @return Collection<Table>
     */
    private function getActiveTables(Restaurant $restaurant): Collection
    {
        return Table::query()
            ->where('restaurant_id', $restaurant->id)
            ->active()
            ->get();
    }

    /**
     * Calculate total seating capacity from tables.
     */
    private function calculateTotalCapacity(Collection $tables): int
    {
        return $tables->sum('seats');
    }

    /**
     * Get reservations for a date that occupy capacity.
     *
     * @return Collection<Reservation>
     */
    private function getReservationsForDate(Restaurant $restaurant, CarbonInterface $date): Collection
    {
        return Reservation::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('date', $date->toDateString())
            ->whereIn('status', self::OCCUPIED_STATUSES)
            ->get();
    }

    /**
     * Generate time slots based on opening hours and constraints.
     *
     * @return array<TimeSlotAvailability>
     */
    private function generateSlots(
        Restaurant $restaurant,
        CarbonInterface $date,
        Collection $openingHours,
        array $blockedRanges,
        Collection $tables,
        Collection $reservations,
        int $partySize,
        int $totalCapacity,
    ): array {
        $slots = [];

        // Calculate lead time cutoff for "today" filtering
        $leadTimeCutoff = $this->calculateLeadTimeCutoff($restaurant, $date);

        foreach ($openingHours as $opening) {
            $openTime = $this->parseTimeOnDate($date, $opening->open_time);
            $closeTime = $this->parseTimeOnDate($date, $opening->close_time);

            // Use last_reservation_time if set, otherwise use close_time
            $lastReservationTime = $opening->last_reservation_time
                ? $this->parseTimeOnDate($date, $opening->last_reservation_time)
                : $closeTime;

            // Handle overnight shifts (close time is next day)
            if ($closeTime->lte($openTime)) {
                $closeTime->addDay();
                $lastReservationTime->addDay();
            }

            // Generate slots at SLOT_INTERVAL_MINUTES intervals
            $current = $openTime->copy();

            while ($current->lt($lastReservationTime)) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes(self::SLOT_INTERVAL_MINUTES);

                // Check if slot is blocked
                if ($this->isTimeBlocked($slotStart->format('H:i'), $blockedRanges)) {
                    $current->addMinutes(self::SLOT_INTERVAL_MINUTES);

                    continue;
                }

                // Skip slots that violate the minimum lead time (for today only)
                if ($leadTimeCutoff !== null && $slotStart->lt($leadTimeCutoff)) {
                    $current->addMinutes(self::SLOT_INTERVAL_MINUTES);

                    continue;
                }

                // Calculate available capacity for this slot
                $occupiedGuests = $this->calculateOccupiedCapacityForSlot(
                    $slotStart,
                    $reservations,
                );

                $availableCapacity = max(0, $totalCapacity - $occupiedGuests);

                // Check if party can fit
                $isBookable = $this->canFitParty(
                    partySize: $partySize,
                    availableCapacity: $availableCapacity,
                    tables: $tables,
                    slotStart: $slotStart,
                    reservations: $reservations,
                );

                $slots[] = new TimeSlotAvailability(
                    start: $slotStart,
                    end: $slotEnd,
                    isBookable: $isBookable,
                    maxPartySizeForSlot: $availableCapacity,
                    debug: [
                        'total_capacity' => $totalCapacity,
                        'occupied_guests' => $occupiedGuests,
                        'available_capacity' => $availableCapacity,
                    ],
                );

                $current->addMinutes(self::SLOT_INTERVAL_MINUTES);
            }
        }

        // Sort slots by start time
        usort($slots, fn ($a, $b) => $a->start->timestamp <=> $b->start->timestamp);

        return $slots;
    }

    /**
     * Calculate the lead time cutoff for "today" filtering.
     *
     * Returns null for future dates (no cutoff needed).
     * For today, returns the current time + lead time in the restaurant's timezone.
     */
    private function calculateLeadTimeCutoff(Restaurant $restaurant, CarbonInterface $date): ?Carbon
    {
        $timezone = $restaurant->timezone ?? config('app.timezone');
        $now = Carbon::now($timezone);

        // Only apply cutoff if the requested date is today in the restaurant's timezone
        if (! $date->isSameDay($now)) {
            return null;
        }

        $leadTimeMinutes = $restaurant->booking_min_lead_time_minutes ?? 0;

        return $now->copy()->addMinutes($leadTimeMinutes);
    }

    /**
     * Parse a time string onto a specific date.
     */
    private function parseTimeOnDate(CarbonInterface $date, mixed $time): Carbon
    {
        if ($time instanceof CarbonInterface) {
            $timeString = $time->format('H:i');
        } else {
            $timeString = (string) $time;
        }

        return $date->copy()->setTimeFromTimeString($timeString);
    }

    /**
     * Check if a time falls within any blocked range.
     */
    private function isTimeBlocked(string $time, array $blockedRanges): bool
    {
        foreach ($blockedRanges as $range) {
            if ($time >= $range['from'] && $time < $range['to']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate how many guests have overlapping reservations for a slot.
     */
    private function calculateOccupiedCapacityForSlot(
        CarbonInterface $slotStart,
        Collection $reservations,
    ): int {
        $occupiedGuests = 0;

        foreach ($reservations as $reservation) {
            if ($this->reservationOverlapsSlot($reservation, $slotStart)) {
                $occupiedGuests += $reservation->guests;
            }
        }

        return $occupiedGuests;
    }

    /**
     * Check if a reservation overlaps with a slot.
     *
     * A reservation overlaps if the slot start time falls within the reservation's
     * time range (from reservation time to reservation time + duration).
     */
    private function reservationOverlapsSlot(Reservation $reservation, CarbonInterface $slotStart): bool
    {
        // Extract the time string from the reservation
        // The time field is cast to datetime:H:i, which gives us a Carbon object
        // but we only care about the H:i portion
        $reservationTime = $reservation->time;
        $timeString = $reservationTime instanceof CarbonInterface
            ? $reservationTime->format('H:i')
            : (string) $reservationTime;

        // Build reservation start on the slot's date
        $reservationStart = $slotStart->copy()->startOfDay()->setTimeFromTimeString($timeString);

        $duration = $reservation->duration_minutes ?? self::DEFAULT_RESERVATION_DURATION_MINUTES;
        $reservationEnd = $reservationStart->copy()->addMinutes($duration);

        $slotTime = $slotStart->copy();

        // Slot overlaps if slot start is within reservation time range
        return $slotTime->gte($reservationStart) && $slotTime->lt($reservationEnd);
    }

    /**
     * Check if a party can fit at the given slot.
     *
     * For a simple first version, we just check if available capacity >= party size.
     * Future versions could use more sophisticated table assignment algorithms.
     *
     * TODO: Future extension - proper table combination algorithm
     * TODO: Future extension - consider table zones and preferences
     */
    private function canFitParty(
        int $partySize,
        int $availableCapacity,
        Collection $tables,
        CarbonInterface $slotStart,
        Collection $reservations,
    ): bool {
        // Simple check: can we fit this party in the remaining capacity?
        if ($partySize > $availableCapacity) {
            return false;
        }

        // Additional check: is there at least one table or combination that could fit?
        // For the first version, we use a greedy approach:
        // - Check if any single table can accommodate the party
        // - Or if we can combine tables (all tables are assumed combinable)

        // Find tables that are not occupied during this slot
        $occupiedTableIds = $this->getOccupiedTableIds($slotStart, $reservations);
        $availableTables = $tables->reject(fn (Table $table) => in_array($table->id, $occupiedTableIds, true));

        if ($availableTables->isEmpty()) {
            return false;
        }

        // Check if a single table can fit the party
        foreach ($availableTables as $table) {
            if ($table->canAccommodate($partySize)) {
                return true;
            }
        }

        // Try to combine tables (greedy approach: use smallest tables first)
        $sortedTables = $availableTables
            ->filter(fn (Table $table) => $table->is_combinable)
            ->sortBy('seats');

        $combinedSeats = 0;

        foreach ($sortedTables as $table) {
            $combinedSeats += $table->seats;

            if ($combinedSeats >= $partySize) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get IDs of tables that are occupied during a slot.
     *
     * @return array<int>
     */
    private function getOccupiedTableIds(CarbonInterface $slotStart, Collection $reservations): array
    {
        $occupiedIds = [];

        foreach ($reservations as $reservation) {
            if ($reservation->table_id && $this->reservationOverlapsSlot($reservation, $slotStart)) {
                $occupiedIds[] = $reservation->table_id;
            }
        }

        return $occupiedIds;
    }
}
