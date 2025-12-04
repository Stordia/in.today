<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Filament\Resources\AffiliateResource\RelationManagers;
use App\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 1;

    /**
     * Hide from sidebar navigation - access via AffiliateHub page instead.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique referral code, e.g. meraki-media'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'agency' => 'Agency',
                                'creator' => 'Creator',
                                'consultant' => 'Consultant',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('other'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'blocked' => 'Blocked',
                            ])
                            ->required()
                            ->default('active'),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Commission')
                    ->schema([
                        Forms\Components\TextInput::make('default_commission_rate')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(10.00)
                            ->helperText('Default commission percentage for conversions'),
                    ])->columns(1),

                Forms\Components\Section::make('Additional')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('metadata')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('JSON format for custom settings'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agency' => 'info',
                        'creator' => 'success',
                        'consultant' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('default_commission_rate')
                    ->label('Commission')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('links_count')
                    ->label('Links')
                    ->counts('links')
                    ->sortable(),
                Tables\Columns\TextColumn::make('conversions_count')
                    ->label('Conversions')
                    ->counts('conversions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('outstanding_approved_commission')
                    ->label('Outstanding')
                    ->money('EUR', locale: 'de_DE')
                    ->getStateUsing(fn (Affiliate $record): float => $record->outstanding_approved_commission)
                    ->sortable(query: fn ($query, $direction) => $query
                        ->withSum(['conversions as outstanding_sum' => fn ($q) => $q->where('status', 'approved')->whereNull('affiliate_payout_id')], 'commission_amount')
                        ->orderBy('outstanding_sum', $direction)
                    )
                    ->description('Approved, not in payout')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_commission')
                    ->label('Paid')
                    ->money('EUR', locale: 'de_DE')
                    ->getStateUsing(fn (Affiliate $record): float => $record->paid_commission)
                    ->sortable(query: fn ($query, $direction) => $query
                        ->withSum(['conversions as paid_sum' => fn ($q) => $q->where('status', 'paid')], 'commission_amount')
                        ->orderBy('paid_sum', $direction)
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'blocked' => 'Blocked',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'agency' => 'Agency',
                        'creator' => 'Creator',
                        'consultant' => 'Consultant',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LinksRelationManager::class,
            RelationManagers\ConversionsRelationManager::class,
            RelationManagers\PayoutsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'view' => Pages\ViewAffiliate::route('/{record}'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }
}
