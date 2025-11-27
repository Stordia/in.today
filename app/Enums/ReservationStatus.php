<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CancelledByCustomer = 'cancelled_by_customer';
    case CancelledByRestaurant = 'cancelled_by_restaurant';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::CancelledByCustomer => 'Cancelled by Customer',
            self::CancelledByRestaurant => 'Cancelled by Restaurant',
            self::Completed => 'Completed',
            self::NoShow => 'No Show',
        };
    }

    public function isCancelled(): bool
    {
        return in_array($this, [self::CancelledByCustomer, self::CancelledByRestaurant], true);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::CancelledByCustomer,
            self::CancelledByRestaurant,
            self::Completed,
            self::NoShow,
        ], true);
    }
}
