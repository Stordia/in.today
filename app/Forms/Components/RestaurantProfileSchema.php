<?php

declare(strict_types=1);

namespace App\Forms\Components;

use App\Models\City;
use App\Models\Country;
use App\Models\Cuisine;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

/**
 * Shared restaurant profile form schema.
 *
 * Provides consistent fields and validation for restaurant core profile
 * across Admin and Business panels.
 */
class RestaurantProfileSchema
{
    /**
     * Get basic information schema (name, cuisine, tagline, description).
     */
    public static function getBasicInformationSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Business Name')
                ->required()
                ->maxLength(255)
                ->helperText('The name displayed on your booking page and in emails.'),

            Select::make('cuisine_id')
                ->label('Main Cuisine')
                ->options(fn () => Cuisine::query()->ordered()->pluck('name_en', 'id'))
                ->searchable()
                ->preload()
                ->placeholder('Select your main cuisine')
                ->helperText('Choose the main cuisine your guests will see on your profile.'),

            TextInput::make('tagline')
                ->label('Tagline')
                ->maxLength(150)
                ->placeholder('e.g., Authentic Italian Cuisine')
                ->helperText('A short phrase describing your business style.'),

            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->maxLength(1000)
                ->helperText('A brief description for your booking page.'),
        ];
    }

    /**
     * Get contact information schema (phone, email, website).
     */
    public static function getContactInformationSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(50)
                        ->placeholder('+49 123 456789')
                        ->helperText('For customer inquiries and reservations.'),

                    TextInput::make('email')
                        ->label('Public Email')
                        ->email()
                        ->maxLength(255)
                        ->placeholder('info@restaurant.com')
                        ->helperText('For customer inquiries.'),
                ]),

            TextInput::make('website_url')
                ->label('Website URL')
                ->maxLength(500)
                ->prefix('https://')
                ->placeholder('meraki.bar')
                ->dehydrateStateUsing(function (?string $state): ?string {
                    if (empty($state)) {
                        return null;
                    }
                    $trimmed = trim($state);
                    if (empty($trimmed)) {
                        return null;
                    }
                    // Add https:// if no scheme is present
                    if (! preg_match('/^https?:\/\//i', $trimmed)) {
                        return 'https://' . $trimmed;
                    }

                    return $trimmed;
                })
                ->helperText('Your website (optional). You can enter just the domain name (e.g. meraki.bar).'),
        ];
    }

    /**
     * Get location/address schema (country, city, street, postal).
     */
    public static function getLocationSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('country_id')
                        ->label('Country')
                        ->options(fn () => Country::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('city_id', null)),

                    Select::make('city_id')
                        ->label('City')
                        ->options(function (Get $get) {
                            $countryId = $get('country_id');
                            if (! $countryId) {
                                return [];
                            }

                            return City::query()
                                ->where('country_id', $countryId)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->live()
                        ->helperText('Select a country first.'),
                ]),

            Grid::make(3)
                ->schema([
                    TextInput::make('address_street')
                        ->label('Street Address')
                        ->maxLength(255)
                        ->columnSpan(2),

                    TextInput::make('address_postal')
                        ->label('Postal Code')
                        ->maxLength(20),
                ]),

            TextInput::make('address_district')
                ->label('District / Neighborhood')
                ->maxLength(255)
                ->helperText('Optional neighborhood or area name.'),
        ];
    }

    /**
     * Get booking configuration schema (enabled, slug, party size, timing).
     */
    public static function getBookingConfigurationSchema(bool $showPublicSlug = true): array
    {
        $schema = [
            TextInput::make('booking_min_party_size')
                ->label('Minimum Party Size')
                ->numeric()
                ->minValue(1)
                ->maxValue(50)
                ->default(1)
                ->required()
                ->suffix('guests')
                ->helperText('Smallest party you accept online.')
                ->live(onBlur: true),

            TextInput::make('booking_max_party_size')
                ->label('Maximum Party Size')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->default(20)
                ->required()
                ->suffix('guests')
                ->helperText('Largest party for online booking.')
                ->rules([
                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                        if ((int) $value < $minPartySize) {
                            $fail("Maximum guests must be at least {$minPartySize}.");
                        }
                    },
                ]),

            TextInput::make('booking_default_duration_minutes')
                ->label('Table Time')
                ->numeric()
                ->minValue(15)
                ->maxValue(480)
                ->default(90)
                ->required()
                ->suffix('minutes')
                ->helperText('How long each reservation blocks the table.'),

            TextInput::make('booking_min_lead_time_minutes')
                ->label('Minimum Notice')
                ->numeric()
                ->minValue(0)
                ->maxValue(1440)
                ->default(60)
                ->required()
                ->suffix('minutes')
                ->helperText('How much advance notice you need.'),

            TextInput::make('booking_max_lead_time_days')
                ->label('Book Ahead')
                ->numeric()
                ->minValue(1)
                ->maxValue(365)
                ->default(30)
                ->required()
                ->suffix('days')
                ->helperText('How far ahead guests can book.'),
        ];

        if ($showPublicSlug) {
            array_unshift($schema, TextInput::make('booking_public_slug')
                ->label('Public Booking URL')
                ->maxLength(100)
                ->helperText('The unique URL slug for your public booking page.'));
        }

        return $schema;
    }
}
