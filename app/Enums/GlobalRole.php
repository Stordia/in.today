<?php

declare(strict_types=1);

namespace App\Enums;

enum GlobalRole: string
{
    case User = 'user';
    case PlatformAdmin = 'platform_admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::PlatformAdmin => 'Platform Admin',
        };
    }
}
