<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\TableResource\Pages;

use App\Filament\Restaurant\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTables extends ListRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
