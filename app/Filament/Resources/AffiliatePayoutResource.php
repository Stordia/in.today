<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AffiliatePayoutStatus;
use App\Filament\Resources\AffiliatePayoutResource\Pages;
use App\Models\AffiliatePayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliatePayoutResource extends Resource
{
    protected static ?string $model = AffiliatePayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Payouts';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payout Details')
                    ->schema([
                        Forms\Components\Select::make('affiliate_id')
                            ->relationship('affiliate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('currency')
                            ->maxLength(3)
                            ->default('EUR')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(AffiliatePayoutStatus::class)
                            ->default(AffiliatePayoutStatus::Pending)
                            ->required(),
                    ])->columns(2),

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
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'paid' || $get('status') === AffiliatePayoutStatus::Paid),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Payout Details')
                                    ->icon('heroicon-o-banknotes')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('affiliate.name')
                                            ->label('Affiliate')
                                            ->weight(FontWeight::SemiBold),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Amount')
                                            ->money(fn (AffiliatePayout $record): string => $record->currency ?? 'EUR', locale: 'de_DE')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn ($state): string => $state instanceof AffiliatePayoutStatus
                                                ? $state->color()
                                                : (AffiliatePayoutStatus::tryFrom((string) $state)?->color() ?? 'gray')
                                            ),
                                        Infolists\Components\TextEntry::make('conversions_count')
                                            ->label('Conversions')
                                            ->badge()
                                            ->color('primary'),
                                    ])
                                    ->columns(2),

                                Infolists\Components\Section::make('Period')
                                    ->icon('heroicon-o-calendar')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('period_start')
                                            ->label('From')
                                            ->date()
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('period_end')
                                            ->label('To')
                                            ->date()
                                            ->placeholder('—'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(2),

                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make('Payment')
                                    ->icon('heroicon-o-credit-card')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('method')
                                            ->label('Method')
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('reference')
                                            ->label('Reference')
                                            ->copyable()
                                            ->placeholder('—'),
                                        Infolists\Components\TextEntry::make('paid_at')
                                            ->label('Paid At')
                                            ->dateTime()
                                            ->placeholder('Not yet paid'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('affiliate.name')
                    ->label('Affiliate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency ?? 'EUR', locale: 'de_DE')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->sortable()
                    ->toggleable(),
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
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('markProcessing')
                    ->label('Processing')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (AffiliatePayout $record): bool => $record->status === AffiliatePayoutStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function (AffiliatePayout $record): void {
                        $record->status = AffiliatePayoutStatus::Processing;
                        $record->save();
                    }),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->color('success')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (AffiliatePayout $record): bool => $record->status === AffiliatePayoutStatus::Pending
                        || $record->status === AffiliatePayoutStatus::Processing
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark Payout as Paid')
                    ->modalDescription('This will mark the payout as paid and set the paid_at timestamp.')
                    ->action(function (AffiliatePayout $record): void {
                        $record->status = AffiliatePayoutStatus::Paid;
                        $record->paid_at = now();
                        $record->save();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (AffiliatePayout $record): bool => $record->status === AffiliatePayoutStatus::Pending
                        || $record->status === AffiliatePayoutStatus::Processing
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Payout')
                    ->modalDescription('This will cancel the payout. Linked conversions will remain linked but you may need to create a new payout.')
                    ->action(function (AffiliatePayout $record): void {
                        $record->status = AffiliatePayoutStatus::Cancelled;
                        $record->save();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliatePayouts::route('/'),
            'create' => Pages\CreateAffiliatePayout::route('/create'),
            'view' => Pages\ViewAffiliatePayout::route('/{record}'),
            'edit' => Pages\EditAffiliatePayout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) AffiliatePayout::query()
            ->where('status', AffiliatePayoutStatus::Pending)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
