<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use Carbon\CarbonInterface;

/**
 * Result object containing all available time slots for a given date and party size.
 */
final readonly class AvailabilityResult
{
    /**
     * @param  array<TimeSlotAvailability>  $slots
     */
    public function __construct(
        public CarbonInterface $date,
        public int $partySize,
        public array $slots,
    ) {}

    /**
     * Check if there are any slots available (bookable or not).
     */
    public function hasAnySlots(): bool
    {
        return count($this->slots) > 0;
    }

    /**
     * Get only the bookable slots.
     *
     * @return array<TimeSlotAvailability>
     */
    public function onlyBookable(): array
    {
        return array_values(array_filter(
            $this->slots,
            fn (TimeSlotAvailability $slot): bool => $slot->isBookable
        ));
    }

    /**
     * Check if there are any bookable slots.
     */
    public function hasBookableSlots(): bool
    {
        return count($this->onlyBookable()) > 0;
    }

    /**
     * Get the total number of slots.
     */
    public function totalSlots(): int
    {
        return count($this->slots);
    }

    /**
     * Get the number of bookable slots.
     */
    public function bookableSlotCount(): int
    {
        return count($this->onlyBookable());
    }

    /**
     * Find a slot by its start time (H:i format).
     */
    public function findSlotByTime(string $time): ?TimeSlotAvailability
    {
        foreach ($this->slots as $slot) {
            if ($slot->getStartTime() === $time) {
                return $slot;
            }
        }

        return null;
    }
}
