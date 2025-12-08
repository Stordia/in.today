<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateRestaurant extends CreateRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate booking_public_slug if booking is enabled and slug is empty
        if (! empty($data['booking_enabled']) && empty($data['booking_public_slug'])) {
            $data['booking_public_slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        return $data;
    }
}
