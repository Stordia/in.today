<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Enums\AffiliateConversionStatus;
use App\Enums\AffiliatePayoutStatus;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Filament\Resources\AffiliateResource;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Models\AffiliatePayout;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\DB;

class ViewAffiliate extends ViewRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createPayoutFromApproved')
                ->label('Create Payout from Approved')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Create Payout from Approved Conversions')
                ->modalDescription('This will create a payout from all approved conversions that are not yet linked to a payout.')
                ->action(function (): void {
                    /** @var Affiliate $affiliate */
                    $affiliate = $this->record;

                    // Find all approved conversions not yet linked to a payout
                    $conversions = AffiliateConversion::query()
                        ->where('affiliate_id', $affiliate->id)
                        ->where('status', AffiliateConversionStatus::Approved)
                        ->whereNull('affiliate_payout_id')
                        ->where('commission_amount', '>', 0)
                        ->get();

                    if ($conversions->isEmpty()) {
                        Notification::make()
                            ->title('No eligible conversions')
                            ->body('There are no approved conversions without a payout for this affiliate.')
                            ->warning()
                            ->send();

                        return;
                    }

                    DB::transaction(function () use ($affiliate, $conversions): void {
                        // Calculate totals
                        $totalAmount = $conversions->sum('commission_amount');
                        $currency = $conversions->first()->currency ?? 'EUR';
                        $periodStart = $conversions->min('occurred_at');
                        $periodEnd = $conversions->max('occurred_at');

                        // Create the payout
                        $payout = AffiliatePayout::create([
                            'affiliate_id' => $affiliate->id,
                            'amount' => $totalAmount,
                            'currency' => $currency,
                            'status' => AffiliatePayoutStatus::Pending,
                            'period_start' => $periodStart?->toDateString(),
                            'period_end' => $periodEnd?->toDateString(),
                        ]);

                        // Link conversions to the payout
                        AffiliateConversion::query()
                            ->whereIn('id', $conversions->pluck('id'))
                            ->update(['affiliate_payout_id' => $payout->id]);

                        Notification::make()
                            ->title('Payout created')
                            ->body(sprintf(
                                'Created payout #%d for %s with %d conversions totaling %.2f %s.',
                                $payout->id,
                                $affiliate->name,
                                $conversions->count(),
                                $totalAmount,
                                $currency
                            ))
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('View Payout')
                                    ->url(AffiliatePayoutResource::getUrl('view', ['record' => $payout]))
                                    ->button(),
                            ])
                            ->send();
                    });
                })
                ->visible(fn (): bool => $this->record->conversions()
                    ->where('status', AffiliateConversionStatus::Approved)
                    ->whereNull('affiliate_payout_id')
                    ->where('commission_amount', '>', 0)
                    ->exists()
                ),
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
                                        Infolists\Components\TextEntry::make('outstanding_approved_commission')
                                            ->label('Outstanding (Approved)')
                                            ->getStateUsing(fn (Affiliate $record): string => number_format($record->outstanding_approved_commission, 2, ',', '.') . ' €')
                                            ->color('info')
                                            ->helperText('Approved but not yet in a payout'),
                                        Infolists\Components\TextEntry::make('total_paid_commission')
                                            ->label('Total Paid')
                                            ->getStateUsing(fn (Affiliate $record): string => number_format($record->paid_commission, 2, ',', '.') . ' €')
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
