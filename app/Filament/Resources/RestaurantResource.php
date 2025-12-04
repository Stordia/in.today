<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RestaurantPlan;
use App\Filament\Resources\RestaurantResource\Pages;
use App\Filament\Resources\RestaurantResource\RelationManagers;
use App\Models\Agency;
use App\Models\City;
use App\Models\Cuisine;
use App\Models\Restaurant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Directory';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Leave blank to auto-generate from name'),
                        Forms\Components\Select::make('agency_id')
                            ->label('Agency')
                            ->relationship('agency', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Direct customer (no agency)'),
                        Forms\Components\Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a country'),
                        Forms\Components\Select::make('city_id')
                            ->label('City')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('cuisine_id')
                            ->label('Cuisine')
                            ->relationship('cuisine', 'name_en')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('timezone')
                            ->label('Timezone')
                            ->options(fn () => array_combine(
                                \DateTimeZone::listIdentifiers(),
                                \DateTimeZone::listIdentifiers()
                            ))
                            ->searchable()
                            ->required()
                            ->default(config('app.timezone', 'Europe/Berlin')),
                    ])->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('address_street')
                            ->label('Street')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_district')
                            ->label('District')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_postal')
                            ->label('Postal Code')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('address_country')
                            ->label('Country')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),
                    ])->columns(3),

                Forms\Components\Section::make('Classification')
                    ->schema([
                        Forms\Components\Select::make('price_range')
                            ->options([
                                1 => '€',
                                2 => '€€',
                                3 => '€€€',
                                4 => '€€€€',
                            ]),
                        Forms\Components\Select::make('plan')
                            ->options(RestaurantPlan::class)
                            ->default(RestaurantPlan::Starter),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured'),
                    ])->columns(3),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('cover_image_url')
                            ->label('Cover Image URL')
                            ->url()
                            ->maxLength(500),
                    ])->columns(2),

                Forms\Components\Section::make('Owner')
                    ->description('Primary owner of this restaurant. Manage team members in the Users tab.')
                    ->schema([
                        Forms\Components\Placeholder::make('owner_info')
                            ->label('Current Owner')
                            ->content(function (?Restaurant $record): string {
                                if (! $record) {
                                    return 'Owner will be assigned after creation (use Onboard for new restaurants).';
                                }

                                $owner = $record->owner();

                                if (! $owner) {
                                    return 'No owner assigned. Use the Users relation manager to assign one.';
                                }

                                return "{$owner->name} ({$owner->email})";
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuisine.name_en')
                    ->label('Cuisine')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('agency.name')
                    ->label('Agency')
                    ->placeholder('Direct')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Owner')
                    ->getStateUsing(fn (Restaurant $record): ?string => $record->owner()?->name)
                    ->placeholder('—')
                    ->url(fn (Restaurant $record): ?string => ($owner = $record->owner())
                        ? UserResource::getUrl('view', ['record' => $owner])
                        : null
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservation_count')
                    ->label('Reservations')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('cuisine_id')
                    ->label('Cuisine')
                    ->relationship('cuisine', 'name_en')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('agency_id')
                    ->label('Agency')
                    ->relationship('agency', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('plan')
                    ->options(RestaurantPlan::class),
            ])
            ->actions([
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
            'onboard' => Pages\OnboardRestaurant::route('/onboard'),
        ];
    }
}
