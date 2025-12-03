<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateConversionResource\Pages;

use App\Filament\Resources\AffiliateConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateConversion extends EditRecord
{
    protected static string $resource = AffiliateConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
