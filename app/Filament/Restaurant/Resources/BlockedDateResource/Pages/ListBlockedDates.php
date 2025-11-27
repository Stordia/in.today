<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BlockedDateResource\Pages;

use App\Filament\Restaurant\Resources\BlockedDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlockedDates extends ListRecords
{
    protected static string $resource = BlockedDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
