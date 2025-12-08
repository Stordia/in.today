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

        return $data;
    }
}
