<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationSource: string
{
    case Platform = 'platform';
    case Widget = 'widget';
    case Phone = 'phone';
    case WalkIn = 'walk_in';
    case Api = 'api';

    public function label(): string
    {
        return match ($this) {
            self::Platform => 'Platform',
            self::Widget => 'Widget',
            self::Phone => 'Phone',
            self::WalkIn => 'Walk-in',
            self::Api => 'API',
        };
    }
}
