<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use App\Models\Country;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Sync legacy country field with the selected country's name
        if (! empty($data['country_id'])) {
            $country = Country::find($data['country_id']);
            $data['country'] = $country?->name;
        } else {
            $data['country'] = null;
        }

        return $data;
    }
}
