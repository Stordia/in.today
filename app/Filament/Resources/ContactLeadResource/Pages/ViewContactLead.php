<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactLeadResource\Pages;

use App\Filament\Resources\ContactLeadResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewContactLead extends ViewRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('email')
                ->label('Email')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->url(fn (): string => ContactLeadResource::getUrl('email', ['record' => $this->record])),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // Get parent infolist schema
        $parentInfolist = parent::infolist($infolist);

        // Add email history section
        return $parentInfolist->schema([
            ...$parentInfolist->getComponents(),

            Section::make('Email History')
                ->icon('heroicon-o-envelope')
                ->collapsed(fn (): bool => $this->record->emails()->count() === 0)
                ->description(fn (): string => $this->record->emails()->count() . ' email(s) sent')
                ->schema([
                    ViewEntry::make('emails_list')
                        ->view('filament.infolists.components.email-history', [
                            'emails' => $this->record->emails()
                                ->with('sentBy')
                                ->orderByDesc('created_at')
                                ->limit(10)
                                ->get(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
