<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRestaurants extends ListRecords
{
    protected static string $resource = RestaurantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('onboard')
                ->label('Onboard Restaurant')
                ->icon('heroicon-o-rocket-launch')
                ->url(RestaurantResource::getUrl('onboard'))
                ->color('success'),
            Actions\CreateAction::make(),
        ];
    }
}
