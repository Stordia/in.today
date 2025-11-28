<?php

declare(strict_types=1);

namespace App\Filament\Resources;

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
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'paid' => 'Paid',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('commission_amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
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
                Tables\Columns\TextColumn::make('contactLead.name')
                    ->label('Lead')
                    ->description(fn ($record) => $record->contactLead?->email)
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money(fn ($record) => $record->currency ?? 'EUR')
                    ->sortable(),
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
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'paid' => 'Paid',
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
}
