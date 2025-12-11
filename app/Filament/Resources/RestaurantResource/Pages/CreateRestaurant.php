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

        // Move form fields into settings JSON column
        $settings = [];
        $settings['tagline'] = $data['tagline'] ?? null;
        $settings['description'] = $data['description'] ?? null;
        $settings['phone'] = $data['phone'] ?? null;
        $settings['email'] = $data['email'] ?? null;
        $settings['website_url'] = $data['website_url'] ?? null;
        $data['settings'] = $settings;

        // Remove the individual fields from $data since they're now in settings
        unset($data['tagline'], $data['description'], $data['phone'], $data['email'], $data['website_url']);

        return $data;
    }
}
