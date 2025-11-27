<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
