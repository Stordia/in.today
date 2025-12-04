<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Enums\AffiliateConversionStatus;
use App\Filament\Resources\AffiliateConversionResource;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\AffiliateConversion;
use App\Services\AppSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConversionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversions';

    protected static ?string $title = 'Conversions';

    protected static ?string $icon = 'heroicon-o-currency-euro';

    public function form(Form $form): Form
    {
        $affiliateId = $this->getOwnerRecord()->getKey();

        return $form
            ->schema([
                Forms\Components\Select::make('affiliate_link_id')
                    ->label('Link')
                    ->options(function () use ($affiliateId): array {
                        return \App\Models\AffiliateLink::query()
                            ->where('affiliate_id', $affiliateId)
                            ->pluck('slug', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('Optional: specific link that generated this conversion'),

                Forms\Components\Select::make('contact_lead_id')
                    ->label('Contact Lead')
                    ->relationship('contactLead', 'email')
                    ->searchable()
                    ->preload()
                    ->helperText('Contact lead associated with this conversion'),

                Forms\Components\Select::make('restaurant_id')
                    ->label('Restaurant')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Restaurant created from this conversion'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(AffiliateConversionStatus::class)
                    ->required()
                    ->default(AffiliateConversionStatus::Pending),

                Forms\Components\TextInput::make('order_amount')
                    ->label('Order Amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€')
                    ->helperText('Base order value (commission is calculated from this)'),

                Forms\Components\TextInput::make('commission_amount')
                    ->label('Commission Amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€')
                    ->helperText('Leave empty to auto-calculate on approval'),

                Forms\Components\TextInput::make('currency')
                    ->maxLength(3)
                    ->default('EUR'),

                Forms\Components\DateTimePicker::make('occurred_at')
                    ->label('Occurred At'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('affiliateLink.slug')
                    ->label('Link')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contactLead.email')
                    ->label('Lead Email')
                    ->description(fn ($record) => $record->contactLead?->name)
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
                Tables\Columns\TextColumn::make('order_amount')
                    ->label('Order')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->placeholder('—')
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('payout.id')
                    ->label('Payout')
                    ->formatStateUsing(fn ($state, $record) => $record->payout ? ('#' . $record->payout->id) : '—')
                    ->url(fn ($record) => $record->payout
                        ? AffiliatePayoutResource::getUrl('view', ['record' => $record->payout])
                        : null
                    )
                    ->openUrlInNewTab()
                    ->toggleable(),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Conversion')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add Conversion')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure affiliate_id is set from the parent record
                        $data['affiliate_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('info')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Pending
                        || $record->status === AffiliateConversionStatus::Rejected
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Conversion')
                    ->modalDescription(fn (AffiliateConversion $record): string => $this->getApproveModalDescription($record))
                    ->action(function (AffiliateConversion $record): void {
                        $this->approveConversion($record);
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

    /**
     * Generate the modal description for approve action.
     */
    private function getApproveModalDescription(AffiliateConversion $record): string
    {
        $lines = ['This will approve the conversion.'];

        $orderAmount = (float) ($record->order_amount ?? 0);
        $commissionAmount = (float) ($record->commission_amount ?? 0);

        if ($commissionAmount > 0) {
            $lines[] = sprintf('Commission: €%.2f (already set).', $commissionAmount);
        } elseif ($orderAmount > 0) {
            $rate = (float) AppSettings::get('affiliate_default_commission_rate', 20);
            $calculatedCommission = round($orderAmount * $rate / 100, 2);
            $lines[] = sprintf(
                'Commission will be auto-calculated: €%.2f × %.0f%% = €%.2f',
                $orderAmount,
                $rate,
                $calculatedCommission
            );
        } else {
            $lines[] = 'No order amount set. Commission will remain empty (set it manually via Edit).';
        }

        return implode("\n", $lines);
    }

    /**
     * Approve a conversion and auto-calculate commission if needed.
     */
    private function approveConversion(AffiliateConversion $record): void
    {
        $orderAmount = (float) ($record->order_amount ?? 0);
        $commissionAmount = (float) ($record->commission_amount ?? 0);

        // Auto-calculate commission if not set and order_amount exists
        if ($commissionAmount <= 0 && $orderAmount > 0) {
            $rate = (float) AppSettings::get('affiliate_default_commission_rate', 20);
            $record->commission_amount = round($orderAmount * $rate / 100, 2);
        }

        if (is_null($record->currency)) {
            $record->currency = 'EUR';
        }

        if (is_null($record->occurred_at)) {
            $record->occurred_at = now();
        }

        $record->status = AffiliateConversionStatus::Approved;
        $record->save();
    }
}
