<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Enums\AffiliateConversionStatus;
use App\Filament\Resources\AffiliateConversionResource;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\AffiliateConversion;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConversionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversions';

    protected static ?string $title = 'Conversions';

    protected static ?string $icon = 'heroicon-o-currency-euro';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('affiliateLink.slug')
                    ->label('Link')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contactLead.email')
                    ->label('Lead Email')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!'),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn ($state): ?string => $state instanceof AffiliateConversionStatus
                        ? $state->icon()
                        : AffiliateConversionStatus::tryFrom((string) $state)?->icon()
                    )
                    ->color(fn ($state): string => $state instanceof AffiliateConversionStatus
                        ? $state->color()
                        : (AffiliateConversionStatus::tryFrom((string) $state)?->color() ?? 'gray')
                    )
                    ->formatStateUsing(fn ($state): string => $state instanceof AffiliateConversionStatus
                        ? $state->label()
                        : (AffiliateConversionStatus::tryFrom((string) $state)?->label() ?? ucfirst((string) $state))
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payout.id')
                    ->label('Payout')
                    ->formatStateUsing(fn ($state, $record) => $record->payout ? ('#' . $record->payout->id) : '—')
                    ->url(fn ($record) => $record->payout
                        ? AffiliatePayoutResource::getUrl('view', ['record' => $record->payout])
                        : null
                    )
                    ->openUrlInNewTab()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Occurred')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\TernaryFilter::make('has_payout')
                    ->label('Has Payout')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('affiliate_payout_id'),
                        false: fn ($query) => $query->whereNull('affiliate_payout_id'),
                    ),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('info')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Pending
                        || $record->status === AffiliateConversionStatus::Rejected
                    )
                    ->requiresConfirmation()
                    ->action(function (AffiliateConversion $record): void {
                        if (is_null($record->commission_amount) && $record->affiliate?->default_commission_rate) {
                            $record->commission_amount = $record->affiliate->default_commission_rate;
                        }
                        if (is_null($record->currency)) {
                            $record->currency = 'EUR';
                        }
                        if (is_null($record->occurred_at)) {
                            $record->occurred_at = now();
                        }
                        $record->status = AffiliateConversionStatus::Approved;
                        $record->save();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Pending
                        || $record->status === AffiliateConversionStatus::Approved
                    )
                    ->requiresConfirmation()
                    ->action(function (AffiliateConversion $record): void {
                        $record->status = AffiliateConversionStatus::Rejected;
                        $record->save();
                    }),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (AffiliateConversion $record): string => AffiliateConversionResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
