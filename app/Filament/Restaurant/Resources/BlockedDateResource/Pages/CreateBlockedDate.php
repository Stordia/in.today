<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BlockedDateResource\Pages;

use App\Filament\Restaurant\Resources\BlockedDateResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateBlockedDate extends CreateRecord
{
    protected static string $resource = BlockedDateResource::class;

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
