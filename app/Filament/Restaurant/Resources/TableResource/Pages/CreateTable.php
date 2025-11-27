<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\TableResource\Pages;

use App\Filament\Restaurant\Resources\TableResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateTable extends CreateRecord
{
    protected static string $resource = TableResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = CurrentRestaurant::id();

        return $data;
    }
}
