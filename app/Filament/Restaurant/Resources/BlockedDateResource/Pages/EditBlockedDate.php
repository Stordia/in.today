<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BlockedDateResource\Pages;

use App\Filament\Restaurant\Resources\BlockedDateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlockedDate extends EditRecord
{
    protected static string $resource = BlockedDateResource::class;

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
