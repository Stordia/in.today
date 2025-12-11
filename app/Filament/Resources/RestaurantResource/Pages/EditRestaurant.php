<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditRestaurant extends EditRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Extract settings fields from JSON column for display in form
        $settings = $data['settings'] ?? [];
        $data['tagline'] = $settings['tagline'] ?? null;
        $data['description'] = $settings['description'] ?? null;
        $data['phone'] = $settings['phone'] ?? null;
        $data['email'] = $settings['email'] ?? null;
        $data['website_url'] = $settings['website_url'] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle booking_public_slug when booking is enabled
        if (! empty($data['booking_enabled'])) {
            if (empty($data['booking_public_slug'])) {
                // If the record already has a slug, preserve it; otherwise generate new one
                if (! empty($this->record->booking_public_slug)) {
                    $data['booking_public_slug'] = $this->record->booking_public_slug;
                } else {
                    $data['booking_public_slug'] = Str::slug($data['name'] ?? $this->record->name) . '-' . Str::random(6);
                }
            }
        }

        // Move form fields into settings JSON column (only if they exist in data)
        $hasSettingsFields = array_key_exists('tagline', $data) ||
                            array_key_exists('description', $data) ||
                            array_key_exists('phone', $data) ||
                            array_key_exists('email', $data) ||
                            array_key_exists('website_url', $data);

        if ($hasSettingsFields) {
            $settings = $this->record->settings ?? [];

            if (array_key_exists('tagline', $data)) {
                $settings['tagline'] = $data['tagline'];
                unset($data['tagline']);
            }
            if (array_key_exists('description', $data)) {
                $settings['description'] = $data['description'];
                unset($data['description']);
            }
            if (array_key_exists('phone', $data)) {
                $settings['phone'] = $data['phone'];
                unset($data['phone']);
            }
            if (array_key_exists('email', $data)) {
                $settings['email'] = $data['email'];
                unset($data['email']);
            }
            if (array_key_exists('website_url', $data)) {
                $settings['website_url'] = $data['website_url'];
                unset($data['website_url']);
            }

            $data['settings'] = $settings;
        }

        return $data;
    }
}
