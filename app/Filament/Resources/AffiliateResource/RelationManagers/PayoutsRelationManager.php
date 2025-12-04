<?php

declare(strict_types=1);

namespace App\Filament\Resources\AffiliateResource\RelationManagers;

use App\Enums\AffiliateConversionStatus;
use App\Enums\AffiliatePayoutStatus;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\AffiliateConversion;
use App\Models\AffiliatePayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PayoutsRelationManager extends RelationManager
{
    protected static string $relationship = 'payouts';

    protected static ?string $title = 'Payouts';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        $affiliateId = $this->getOwnerRecord()->getKey();

        return $form
            ->schema([
                Forms\Components\Section::make('Payout Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->required()
                            ->live()
                            ->helperText('Total payout amount (will be auto-calculated if conversions are selected)'),

                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('EUR')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(AffiliatePayoutStatus::class)
                            ->default(AffiliatePayoutStatus::Pending)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Period & Payment')
                    ->schema([
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Period Start'),

                        Forms\Components\DatePicker::make('period_end')
                            ->label('Period End'),

                        Forms\Components\TextInput::make('method')
                            ->label('Payment Method')
                            ->placeholder('e.g. bank_transfer, paypal')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('reference')
                            ->label('Reference')
                            ->placeholder('e.g. transfer ID')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->visible(fn (Get $get): bool => $get('status') === 'paid' || $get('status') === AffiliatePayoutStatus::Paid),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Select Conversions')
                    ->description('Select approved conversions to include in this payout. Their status will be updated to "paid" when the payout is saved.')
                    ->schema([
                        Forms\Components\CheckboxList::make('conversion_ids')
                            ->label('Approved Conversions')
                            ->options(function () use ($affiliateId): array {
                                return AffiliateConversion::query()
                                    ->where('affiliate_id', $affiliateId)
                                    ->where('status', AffiliateConversionStatus::Approved)
                                    ->whereNull('affiliate_payout_id')
                                    ->get()
                                    ->mapWithKeys(function (AffiliateConversion $conversion): array {
                                        $label = sprintf(
                                            '#%d - %s - €%.2f',
                                            $conversion->id,
                                            $conversion->contactLead?->email ?? $conversion->affiliateLink?->slug ?? 'Unknown',
                                            $conversion->commission_amount ?? 0
                                        );

                                        return [$conversion->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                if (empty($state)) {
                                    return;
                                }

                                $total = AffiliateConversion::whereIn('id', $state)
                                    ->sum('commission_amount');

                                $set('amount', number_format((float) $total, 2, '.', ''));
                            })
                            ->helperText('Check conversions to include. The amount will be auto-calculated.')
                            ->columnSpanFull(),
                    ])
                    ->hiddenOn('edit'),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Payout')
                    ->icon('heroicon-o-plus')
                    ->using(function (array $data): AffiliatePayout {
                        return $this->createPayoutWithConversions($data);
                    })
                    ->after(function (): void {
                        Notification::make()
                            ->title('Payout created')
                            ->body('Selected conversions have been marked as paid.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->color('success')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (AffiliatePayout $record): bool => $record->status === AffiliatePayoutStatus::Pending
                        || $record->status === AffiliatePayoutStatus::Processing
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark Payout as Paid')
                    ->modalDescription('This will mark the payout as paid and update linked conversions.')
                    ->action(function (AffiliatePayout $record): void {
                        $record->status = AffiliatePayoutStatus::Paid;
                        $record->paid_at = now();
                        $record->save();

                        // Also mark all linked conversions as paid
                        $record->conversions()->update([
                            'status' => AffiliateConversionStatus::Paid,
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (AffiliatePayout $record): string => AffiliatePayoutResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Create a payout and attach selected conversions.
     */
    private function createPayoutWithConversions(array $data): AffiliatePayout
    {
        $conversionIds = $data['conversion_ids'] ?? [];
        unset($data['conversion_ids']);

        // Set affiliate_id from the owner record
        $data['affiliate_id'] = $this->getOwnerRecord()->getKey();

        // Create the payout
        $payout = AffiliatePayout::create($data);

        // Attach conversions to this payout and mark them as paid
        if (! empty($conversionIds)) {
            AffiliateConversion::whereIn('id', $conversionIds)
                ->where('affiliate_id', $data['affiliate_id'])
                ->where('status', AffiliateConversionStatus::Approved)
                ->whereNull('affiliate_payout_id')
                ->update([
                    'affiliate_payout_id' => $payout->id,
                    'status' => AffiliateConversionStatus::Paid,
                ]);
        }

        return $payout;
    }
}
