<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateConversionResource\Pages;

use App\Filament\Resources\AffiliateConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateConversions extends ListRecords
{
    protected static string $resource = AffiliateConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
