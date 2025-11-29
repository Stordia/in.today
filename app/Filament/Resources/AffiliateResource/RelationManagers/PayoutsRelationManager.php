<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Enums\AffiliatePayoutStatus;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\AffiliatePayout;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PayoutsRelationManager extends RelationManager
{
    protected static string $relationship = 'payouts';

    protected static ?string $title = 'Payouts';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn ($state): ?string => $state instanceof AffiliatePayoutStatus
                        ? $state->icon()
                        : AffiliatePayoutStatus::tryFrom((string) $state)?->icon()
                    )
                    ->color(fn ($state): string => $state instanceof AffiliatePayoutStatus
                        ? $state->color()
                        : (AffiliatePayoutStatus::tryFrom((string) $state)?->color() ?? 'gray')
                    )
                    ->formatStateUsing(fn ($state): string => $state instanceof AffiliatePayoutStatus
                        ? $state->label()
                        : (AffiliatePayoutStatus::tryFrom((string) $state)?->label() ?? ucfirst((string) $state))
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversions_count')
                    ->label('Conversions')
                    ->counts('conversions')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Period Start')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Period End')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->color('success')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (AffiliatePayout $record): bool => $record->status === AffiliatePayoutStatus::Pending
                        || $record->status === AffiliatePayoutStatus::Processing
                    )
                    ->requiresConfirmation()
                    ->action(function (AffiliatePayout $record): void {
                        $record->status = AffiliatePayoutStatus::Paid;
                        $record->paid_at = now();
                        $record->save();
                    }),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (AffiliatePayout $record): string => AffiliatePayoutResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
