<?php

declare(strict_types=1);

namespace App\Enums;

enum WaitlistStatus: string
{
    case Waiting = 'waiting';
    case Notified = 'notified';
    case Converted = 'converted';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::Notified => 'Notified',
            self::Converted => 'Converted',
            self::Expired => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Waiting, self::Notified], true);
    }
}
