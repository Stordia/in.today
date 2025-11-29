<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AffiliateConversionStatus;
use App\Filament\Resources\AffiliateConversionResource\Pages;
use App\Models\AffiliateConversion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateConversionResource extends Resource
{
    protected static ?string $model = AffiliateConversion::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Conversions';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversion Details')
                    ->schema([
                        Forms\Components\Select::make('affiliate_id')
                            ->relationship('affiliate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('affiliate_link_id')
                            ->relationship('affiliateLink', 'slug')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: specific link that generated this conversion'),
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Restaurant created from this conversion'),
                        Forms\Components\Select::make('contact_lead_id')
                            ->relationship('contactLead', 'email')
                            ->searchable()
                            ->preload()
                            ->helperText('Contact lead associated with this conversion'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Commission')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(AffiliateConversionStatus::class)
                            ->required()
                            ->default(AffiliateConversionStatus::Pending),
                        Forms\Components\TextInput::make('commission_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText('Leave empty to use affiliate default rate on approval'),
                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('EUR'),
                        Forms\Components\DateTimePicker::make('occurred_at')
                            ->label('Occurred At'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional')
                    ->schema([
                        Forms\Components\Textarea::make('metadata')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('JSON format for extra conversion data'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('affiliateLink.slug')
                    ->label('Link')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contactLead.email')
                    ->label('Lead Email')
                    ->description(fn ($record) => $record->contactLead?->name)
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->placeholder('—')
                    ->searchable()
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
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_lead')
                    ->label('Has Lead')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('contact_lead_id'),
                        false: fn ($query) => $query->whereNull('contact_lead_id'),
                    ),
                Tables\Filters\TernaryFilter::make('has_restaurant')
                    ->label('Has Restaurant')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('restaurant_id'),
                        false: fn ($query) => $query->whereNull('restaurant_id'),
                    ),
                Tables\Filters\TernaryFilter::make('has_payout')
                    ->label('Has Payout')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('affiliate_payout_id'),
                        false: fn ($query) => $query->whereNull('affiliate_payout_id'),
                    ),
                Tables\Filters\Filter::make('occurred_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->where('occurred_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->where('occurred_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('markApproved')
                    ->label('Approve')
                    ->color('info')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Pending
                        || $record->status === AffiliateConversionStatus::Rejected
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Conversion')
                    ->modalDescription('This will approve the conversion and set the commission amount if not already set.')
                    ->action(function (AffiliateConversion $record): void {
                        // Fill commission_amount from affiliate default if null
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
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->color('success')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Approved)
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Paid')
                    ->modalDescription('This will mark the conversion as paid to the affiliate.')
                    ->action(function (AffiliateConversion $record): void {
                        $record->status = AffiliateConversionStatus::Paid;
                        $record->save();
                    }),
                Tables\Actions\Action::make('markRejected')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (AffiliateConversion $record): bool => $record->status === AffiliateConversionStatus::Pending
                        || $record->status === AffiliateConversionStatus::Approved
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Reject Conversion')
                    ->modalDescription('This will reject the conversion. The affiliate will not receive commission for this lead.')
                    ->action(function (AffiliateConversion $record): void {
                        $record->status = AffiliateConversionStatus::Rejected;
                        $record->save();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateConversions::route('/'),
            'create' => Pages\CreateAffiliateConversion::route('/create'),
            'view' => Pages\ViewAffiliateConversion::route('/{record}'),
            'edit' => Pages\EditAffiliateConversion::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) AffiliateConversion::query()
            ->where('status', AffiliateConversionStatus::Pending)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
