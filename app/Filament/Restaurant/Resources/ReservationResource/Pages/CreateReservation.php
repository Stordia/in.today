<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\ReservationResource\Pages;

use App\Enums\ReservationSource;
use App\Filament\Restaurant\Resources\ReservationResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = CurrentRestaurant::id();

        // Default source if not set
        if (empty($data['source'])) {
            $data['source'] = ReservationSource::Phone;
        }

        return $data;
    }
}
