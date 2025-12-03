<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Models\City;
use App\Models\Country;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Restaurant Profile page for the Business panel.
 *
 * Allows restaurant owners/managers to edit core information about their restaurant
 * including basic info, contact details, location, and timezone.
 */
class RestaurantProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Restaurant Profile';

    protected static ?string $title = 'Restaurant Profile';

    protected static string $view = 'filament.restaurant.pages.restaurant-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            $this->data = [];

            return;
        }

        $this->form->fill([
            // Basic Info
            'name' => $restaurant->name,
            'slug' => $restaurant->slug,
            'tagline' => $restaurant->settings['tagline'] ?? null,
            'description' => $restaurant->settings['description'] ?? null,
            // Contact
            'phone' => $restaurant->settings['phone'] ?? null,
            'email' => $restaurant->settings['email'] ?? null,
            'website_url' => $restaurant->settings['website_url'] ?? null,
            // Location
            'country_id' => $restaurant->country_id,
            'city_id' => $restaurant->city_id,
            'address_street' => $restaurant->address_street,
            'address_district' => $restaurant->address_district,
            'address_postal' => $restaurant->address_postal,
            // Timezone
            'timezone' => $restaurant->timezone,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return 'Restaurant Profile';
    }

    public function getSubheading(): ?string
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'Please select a restaurant to manage its profile.';
        }

        return "Manage profile information for {$restaurant->name}";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Info')
                    ->description('Core information about your restaurant.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Restaurant Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->disabled()
                            ->helperText('The URL-friendly identifier for your restaurant. Contact support to change this.'),

                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(150)
                            ->helperText('A short phrase describing your restaurant (e.g., "Authentic Italian Cuisine")'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('A brief marketing description of your restaurant.'),
                    ])
                    ->columns(1),

                Section::make('Contact')
                    ->description('Public contact information for customers.')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(50)
                            ->helperText('The phone number customers can call for reservations or inquiries.'),

                        TextInput::make('email')
                            ->label('Public Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('The public email address for customer inquiries.'),

                        TextInput::make('website_url')
                            ->label('Website URL')
                            ->url()
                            ->maxLength(500)
                            ->prefix('https://')
                            ->helperText('Your restaurant\'s website address.'),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->description('Physical address and location details.')
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
                            ->preload()
                            ->required()
                            ->helperText('Select a country first to see available cities.'),

                        TextInput::make('address_street')
                            ->label('Street Address')
                            ->maxLength(255),

                        TextInput::make('address_district')
                            ->label('District / Neighborhood')
                            ->maxLength(255),

                        TextInput::make('address_postal')
                            ->label('Postal Code')
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Timezone & Hours')
                    ->description('Timezone settings for your restaurant.')
                    ->schema([
                        Select::make('timezone')
                            ->label('Timezone')
                            ->options($this->getTimezoneOptions())
                            ->searchable()
                            ->required()
                            ->helperText('All reservation times will be displayed in this timezone.'),

                        Placeholder::make('opening_hours_info')
                            ->label('Opening Hours')
                            ->content(new HtmlString(
                                '<span class="text-sm text-gray-500 dark:text-gray-400">' .
                                'Opening hours are managed in the <a href="' . route('filament.business.resources.opening-hours.index') . '" class="text-primary-600 hover:underline">Opening Hours</a> section.' .
                                '</span>'
                            )),
                    ])
                    ->columns(1),

                Section::make('Public Links')
                    ->description('Your restaurant\'s public URLs.')
                    ->schema([
                        Placeholder::make('booking_url')
                            ->label('Public Booking URL')
                            ->content(function () {
                                $restaurant = CurrentRestaurant::get();
                                if (! $restaurant || ! $restaurant->booking_enabled || ! $restaurant->booking_public_slug) {
                                    return new HtmlString(
                                        '<span class="text-gray-400">Booking is not enabled. Enable it in ' .
                                        '<a href="' . route('filament.business.pages.booking-settings') . '" class="text-primary-600 hover:underline">Booking Settings</a>.</span>'
                                    );
                                }

                                $url = url("/book/{$restaurant->booking_public_slug}");

                                return new HtmlString(
                                    '<a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline">' . $url . '</a>' .
                                    '<button type="button" onclick="navigator.clipboard.writeText(\'' . $url . '\'); this.innerText=\'Copied!\'; setTimeout(() => this.innerText=\'Copy\', 2000);" ' .
                                    'class="ml-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Copy</button>'
                                );
                            }),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            Notification::make()
                ->title('No Restaurant Selected')
                ->body('Please select a restaurant first.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        // Extract settings fields
        $settings = $restaurant->settings ?? [];
        $settings['tagline'] = $data['tagline'] ?? null;
        $settings['description'] = $data['description'] ?? null;
        $settings['phone'] = $data['phone'] ?? null;
        $settings['email'] = $data['email'] ?? null;
        $settings['website_url'] = $data['website_url'] ?? null;

        $restaurant->update([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'city_id' => $data['city_id'],
            'address_street' => $data['address_street'] ?? null,
            'address_district' => $data['address_district'] ?? null,
            'address_postal' => $data['address_postal'] ?? null,
            'timezone' => $data['timezone'],
            'settings' => $settings,
        ]);

        Notification::make()
            ->title('Profile Updated')
            ->body('Restaurant profile has been updated successfully.')
            ->success()
            ->send();
    }

    public function hasRestaurant(): bool
    {
        return CurrentRestaurant::get() !== null;
    }

    /**
     * Get timezone options for the select field.
     *
     * @return array<string, string>
     */
    private function getTimezoneOptions(): array
    {
        $timezones = [
            'Europe/Berlin' => 'Europe/Berlin (CET/CEST)',
            'Europe/London' => 'Europe/London (GMT/BST)',
            'Europe/Paris' => 'Europe/Paris (CET/CEST)',
            'Europe/Rome' => 'Europe/Rome (CET/CEST)',
            'Europe/Madrid' => 'Europe/Madrid (CET/CEST)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
            'Europe/Athens' => 'Europe/Athens (EET/EEST)',
            'Europe/Vienna' => 'Europe/Vienna (CET/CEST)',
            'Europe/Brussels' => 'Europe/Brussels (CET/CEST)',
            'Europe/Zurich' => 'Europe/Zurich (CET/CEST)',
            'Europe/Stockholm' => 'Europe/Stockholm (CET/CEST)',
            'Europe/Oslo' => 'Europe/Oslo (CET/CEST)',
            'Europe/Copenhagen' => 'Europe/Copenhagen (CET/CEST)',
            'Europe/Helsinki' => 'Europe/Helsinki (EET/EEST)',
            'Europe/Warsaw' => 'Europe/Warsaw (CET/CEST)',
            'Europe/Prague' => 'Europe/Prague (CET/CEST)',
            'Europe/Budapest' => 'Europe/Budapest (CET/CEST)',
            'Europe/Lisbon' => 'Europe/Lisbon (WET/WEST)',
            'Europe/Dublin' => 'Europe/Dublin (GMT/IST)',
            'Europe/Moscow' => 'Europe/Moscow (MSK)',
            'America/New_York' => 'America/New_York (EST/EDT)',
            'America/Chicago' => 'America/Chicago (CST/CDT)',
            'America/Denver' => 'America/Denver (MST/MDT)',
            'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
            'America/Toronto' => 'America/Toronto (EST/EDT)',
            'America/Vancouver' => 'America/Vancouver (PST/PDT)',
            'America/Mexico_City' => 'America/Mexico_City (CST/CDT)',
            'America/Sao_Paulo' => 'America/Sao_Paulo (BRT)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST)',
            'Asia/Hong_Kong' => 'Asia/Hong_Kong (HKT)',
            'Asia/Singapore' => 'Asia/Singapore (SGT)',
            'Asia/Dubai' => 'Asia/Dubai (GST)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST)',
            'Australia/Sydney' => 'Australia/Sydney (AEST/AEDT)',
            'Australia/Melbourne' => 'Australia/Melbourne (AEST/AEDT)',
            'Pacific/Auckland' => 'Pacific/Auckland (NZST/NZDT)',
            'UTC' => 'UTC',
        ];

        return $timezones;
    }
}
