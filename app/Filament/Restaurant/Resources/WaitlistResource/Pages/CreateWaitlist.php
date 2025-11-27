<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\WaitlistResource\Pages;

use App\Filament\Restaurant\Resources\WaitlistResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateWaitlist extends CreateRecord
{
    protected static string $resource = WaitlistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = CurrentRestaurant::id();

        return $data;
    }
}
