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
use Filament\Forms\Get;
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
                    ->description('Logo and cover images used on booking pages and emails.')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->imagePreviewHeight('80')
                            ->disk('public')
                            ->directory('restaurants/logos')
                            ->maxSize(2048)
                            ->openable()
                            ->downloadable()
                            ->helperText('Recommended: 512×512px, square format. Max 2MB.'),
                        Forms\Components\FileUpload::make('cover_image_url')
                            ->label('Cover Image')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->disk('public')
                            ->directory('restaurants/covers')
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            ->helperText('Recommended: 1920×1080px, landscape format. Max 5MB.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

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

                Forms\Components\Section::make('Bookings')
                    ->description('Control how many guests can book online and which URL is used for the booking widget.')
                    ->schema([
                        Forms\Components\Toggle::make('booking_enabled')
                            ->label('Enable online bookings')
                            ->helperText('If disabled, the public booking page for this restaurant will not accept new reservations.')
                            ->default(true)
                            ->live(),

                        Forms\Components\TextInput::make('booking_public_slug')
                            ->label('Public booking URL slug')
                            ->helperText('Used for /book/{slug}. Leave empty to auto-generate from the restaurant name on save.')
                            ->maxLength(100)
                            ->visible(fn (Get $get) => $get('booking_enabled')),

                        Forms\Components\TextInput::make('booking_min_party_size')
                            ->label('Minimum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(2)
                            ->required()
                            ->suffix('guests')
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('booking_max_party_size')
                            ->label('Maximum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(8)
                            ->required()
                            ->suffix('guests')
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                    if ((int) $value < $minPartySize) {
                                        $fail("The Maximum Party Size must be at least {$minPartySize} (the minimum party size).");
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('booking_default_duration_minutes')
                            ->label('Default Reservation Duration')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(90)
                            ->required()
                            ->suffix('minutes'),

                        Forms\Components\TextInput::make('booking_min_lead_time_minutes')
                            ->label('Minimum Lead Time')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440)
                            ->default(60)
                            ->required()
                            ->suffix('minutes')
                            ->helperText('How far in advance a reservation must be made.'),

                        Forms\Components\TextInput::make('booking_max_lead_time_days')
                            ->label('Maximum Lead Time')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(30)
                            ->required()
                            ->suffix('days')
                            ->helperText('How far into the future reservations can be made.'),

                        Forms\Components\Textarea::make('booking_notes_internal')
                            ->label('Internal Booking Notes')
                            ->rows(2)
                            ->maxLength(1000)
                            ->helperText('Private notes for staff about booking policies.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Deposit Settings')
                    ->description('Require a deposit for larger groups (manual payment via IBAN, PayPal, etc.).')
                    ->schema([
                        Forms\Components\Toggle::make('booking_deposit_enabled')
                            ->label('Enable deposit requirement')
                            ->helperText('When enabled, larger groups will be required to pay a deposit.')
                            ->default(false)
                            ->live(),

                        Forms\Components\TextInput::make('booking_deposit_threshold_party_size')
                            ->label('Deposit threshold (party size)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(4)
                            ->suffix('guests')
                            ->helperText('Deposit is required for this party size and above.')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled'))
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                    if ((int) $value < $minPartySize) {
                                        $fail("The deposit threshold must be at least {$minPartySize} (the minimum party size).");
                                    }
                                },
                            ]),

                        Forms\Components\Select::make('booking_deposit_type')
                            ->label('Deposit type')
                            ->options([
                                'fixed_per_person' => 'Per person',
                                'fixed_per_reservation' => 'Per reservation',
                            ])
                            ->default('fixed_per_person')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        Forms\Components\TextInput::make('booking_deposit_amount')
                            ->label('Deposit amount')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->default(0)
                            ->prefix('€')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        Forms\Components\TextInput::make('booking_deposit_currency')
                            ->label('Currency')
                            ->maxLength(3)
                            ->default('EUR')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        Forms\Components\Textarea::make('booking_deposit_policy')
                            ->label('Deposit policy')
                            ->rows(3)
                            ->maxLength(2000)
                            ->helperText('Explain your deposit / cancellation policy. This is shown to guests when a deposit is required.')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
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
