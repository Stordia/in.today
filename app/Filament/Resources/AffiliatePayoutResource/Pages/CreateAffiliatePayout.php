<?php

namespace App\Filament\Resources\AffiliatePayoutResource\Pages;

use App\Filament\Resources\AffiliatePayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAffiliatePayout extends CreateRecord
{
    protected static string $resource = AffiliatePayoutResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
