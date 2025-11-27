<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateLinkResource\Pages;

use App\Filament\Resources\AffiliateLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateLinks extends ListRecords
{
    protected static string $resource = AffiliateLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
