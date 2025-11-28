<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactLeadResource\Pages;

use App\Filament\Resources\ContactLeadResource;
use App\Models\ContactLead;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContactLead extends ViewRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reply_email')
                ->label('Reply via Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(fn (ContactLead $record): string => $this->buildMailtoUrl($record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }

    private function buildMailtoUrl(ContactLead $record): string
    {
        $subject = 'in.today – Your website & booking request';

        $restaurantName = $record->restaurant_name ?? 'your restaurant';
        $body = "Hi {$record->name},\n\nThank you for your interest in in.today for {$restaurantName}.\n\n";

        return 'mailto:' . rawurlencode($record->email)
            . '?subject=' . rawurlencode($subject)
            . '&body=' . rawurlencode($body);
    }
}
