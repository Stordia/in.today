<?php

declare(strict_types=1);

namespace App\Enums;

enum RestaurantPlan: string
{
    case Starter = 'starter';
    case Pro = 'pro';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Pro => 'Pro',
            self::Business => 'Business',
        };
    }
}
