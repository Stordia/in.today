<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use Carbon\CarbonInterface;

/**
 * Represents the availability status of a single time slot.
 */
final readonly class TimeSlotAvailability
{
    public function __construct(
        public CarbonInterface $start,
        public CarbonInterface $end,
        public bool $isBookable,
        public int $maxPartySizeForSlot,
        public array $debug = [],
    ) {}

    /**
     * Create a bookable slot.
     */
    public static function bookable(
        CarbonInterface $start,
        CarbonInterface $end,
        int $maxPartySizeForSlot,
        array $debug = [],
    ): self {
        return new self(
            start: $start,
            end: $end,
            isBookable: true,
            maxPartySizeForSlot: $maxPartySizeForSlot,
            debug: $debug,
        );
    }

    /**
     * Create a non-bookable slot.
     */
    public static function notBookable(
        CarbonInterface $start,
        CarbonInterface $end,
        int $maxPartySizeForSlot = 0,
        array $debug = [],
    ): self {
        return new self(
            start: $start,
            end: $end,
            isBookable: false,
            maxPartySizeForSlot: $maxPartySizeForSlot,
            debug: $debug,
        );
    }

    /**
     * Get the slot start time formatted as H:i.
     */
    public function getStartTime(): string
    {
        return $this->start->format('H:i');
    }

    /**
     * Get the slot end time formatted as H:i.
     */
    public function getEndTime(): string
    {
        return $this->end->format('H:i');
    }
}
