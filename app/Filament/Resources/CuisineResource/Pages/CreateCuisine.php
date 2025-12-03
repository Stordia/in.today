<?php

declare(strict_types=1);

namespace App\Filament\Resources\CuisineResource\Pages;

use App\Filament\Resources\CuisineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCuisine extends CreateRecord
{
    protected static string $resource = CuisineResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
