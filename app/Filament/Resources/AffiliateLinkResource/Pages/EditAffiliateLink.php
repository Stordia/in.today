<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateLinkResource\Pages;

use App\Filament\Resources\AffiliateLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateLink extends EditRecord
{
    protected static string $resource = AffiliateLinkResource::class;

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
