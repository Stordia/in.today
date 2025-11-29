<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Filament\Resources\AffiliateResource;
use App\Models\Affiliate;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewAffiliate extends ViewRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Basic Information')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('code')
                                            ->label('Code')
                                            ->copyable()
                                            ->weight(FontWeight::SemiBold),
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Name')
                                            ->weight(FontWeight::SemiBold),
                                        Infolists\Components\TextEntry::make('type')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'agency' => 'info',
                                                'creator' => 'success',
                                                'consultant' => 'warning',
                                                default => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'paused' => 'warning',
                                                'blocked' => 'danger',
                                                default => 'gray',
                                            }),
                                    ])
                                    ->columns(2),

                                Infolists\Components\Section::make('Contact Information')
                                    ->icon('heroicon-o-envelope')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('contact_name')
                                            ->label('Contact Name')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('contact_email')
                                            ->label('Contact Email')
                                            ->copyable()
                                            ->placeholder('—'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(2),

                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Performance')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('links_count')
                                            ->label('Active Links')
                                            ->getStateUsing(fn (Affiliate $record): int => $record->links()->count())
                                            ->badge()
                                            ->color('gray'),
                                        Infolists\Components\TextEntry::make('total_conversions')
                                            ->label('Total Conversions')
                                            ->getStateUsing(fn (Affiliate $record): int => $record->total_conversions)
                                            ->badge()
                                            ->color('primary'),
                                        Infolists\Components\TextEntry::make('pending_conversions')
                                            ->label('Pending Conversions')
                                            ->getStateUsing(fn (Affiliate $record): int => $record->pending_conversions_count)
                                            ->badge()
                                            ->color('warning'),
                                    ]),

                                Infolists\Components\Section::make('Commission Summary')
                                    ->icon('heroicon-o-currency-euro')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('default_commission_rate')
                                            ->label('Default Rate')
                                            ->suffix('%'),
                                        Infolists\Components\TextEntry::make('total_approved_commission')
                                            ->label('Approved (Unpaid)')
                                            ->getStateUsing(fn (Affiliate $record): string => number_format($record->total_approved_commission, 2, ',', '.') . ' €')
                                            ->color('info'),
                                        Infolists\Components\TextEntry::make('total_paid_commission')
                                            ->label('Total Paid')
                                            ->getStateUsing(fn (Affiliate $record): string => number_format($record->total_paid_commission, 2, ',', '.') . ' €')
                                            ->color('success'),
                                    ]),

                                Infolists\Components\Section::make('Notes')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('notes')
                                            ->hiddenLabel()
                                            ->placeholder('No notes'),
                                    ])
                                    ->collapsed(),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
