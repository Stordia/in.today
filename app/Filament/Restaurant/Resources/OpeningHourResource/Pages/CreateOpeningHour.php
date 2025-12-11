<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\OpeningHourResource\Pages;

use App\Filament\Restaurant\Resources\OpeningHourResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = CurrentRestaurant::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
