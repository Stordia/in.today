<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\WaitlistResource\Pages;

use App\Filament\Restaurant\Resources\WaitlistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaitlists extends ListRecords
{
    protected static string $resource = WaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
