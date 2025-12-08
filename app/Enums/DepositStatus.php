<?php

declare(strict_types=1);

namespace App\Enums;

enum DepositStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Paid = 'paid';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::None => 'No Deposit',
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Waived => 'Waived',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function requiresAction(): bool
    {
        return $this === self::Pending;
    }
}
