<?php

declare(strict_types=1);

namespace App\Filament\Resources\WaitlistResource\Pages;

use App\Filament\Resources\WaitlistResource;
use Filament\Resources\Pages\ListRecords;

class ListWaitlists extends ListRecords
{
    protected static string $resource = WaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
