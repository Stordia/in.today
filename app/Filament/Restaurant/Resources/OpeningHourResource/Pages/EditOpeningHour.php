<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\OpeningHourResource\Pages;

use App\Filament\Restaurant\Resources\OpeningHourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpeningHour extends EditRecord
{
    protected static string $resource = OpeningHourResource::class;

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
}
