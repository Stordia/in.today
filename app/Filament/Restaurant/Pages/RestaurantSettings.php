<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Models\City;
use App\Models\Country;
use App\Models\Cuisine;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Unified Restaurant Settings page for the Business panel.
 *
 * Combines Profile, Booking, and Deposit settings in a single tabbed interface
 * for restaurant owners/managers.
 */
class RestaurantSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Business Settings';

    protected static string $view = 'filament.restaurant.pages.restaurant-settings';

    public ?array $data = [];

    public string $activeTab = 'profile';

    /**
     * Currency symbols map for display.
     */
    private const CURRENCY_SYMBOLS = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'CHF' => 'CHF',
        'PLN' => 'zł',
        'CZK' => 'Kč',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
    ];

    public function mount(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            $this->data = [];

            return;
        }

        $this->form->fill([
            // Profile Tab
            'name' => $restaurant->name,
            'slug' => $restaurant->slug,
            'cuisine_id' => $restaurant->cuisine_id,
            'tagline' => $restaurant->settings['tagline'] ?? null,
            'description' => $restaurant->settings['description'] ?? null,
            'phone' => $restaurant->settings['phone'] ?? null,
            'email' => $restaurant->settings['email'] ?? null,
            'website_url' => $restaurant->settings['website_url'] ?? null,
            'country_id' => $restaurant->country_id,
            'city_id' => $restaurant->city_id,
            'address_street' => $restaurant->address_street,
            'address_district' => $restaurant->address_district,
            'address_postal' => $restaurant->address_postal,
            'timezone' => $restaurant->timezone,

            // Bookings Tab
            'booking_enabled' => $restaurant->booking_enabled,
            'booking_public_slug' => $restaurant->booking_public_slug,
            'booking_min_party_size' => $restaurant->booking_min_party_size ?? 1,
            'booking_max_party_size' => $restaurant->booking_max_party_size ?? 20,
            'booking_default_duration_minutes' => $restaurant->booking_default_duration_minutes ?? 90,
            'booking_min_lead_time_minutes' => $restaurant->booking_min_lead_time_minutes ?? 60,
            'booking_max_lead_time_days' => $restaurant->booking_max_lead_time_days ?? 30,
            'booking_notes_internal' => $restaurant->booking_notes_internal,

            // Deposit Tab
            'booking_deposit_enabled' => $restaurant->booking_deposit_enabled ?? false,
            'booking_deposit_threshold_party_size' => $restaurant->booking_deposit_threshold_party_size ?? 4,
            'booking_deposit_type' => $restaurant->booking_deposit_type ?? 'fixed_per_person',
            'booking_deposit_amount' => $restaurant->booking_deposit_amount ?? 10,
            'booking_deposit_currency' => $restaurant->booking_deposit_currency ?? 'EUR',
            'booking_deposit_policy' => $restaurant->booking_deposit_policy,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return 'Settings';
    }

    public function getSubheading(): ?string
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'Please select a restaurant to manage its settings.';
        }

        return "Manage settings for {$restaurant->name}";
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Profile Tab
                Group::make()
                    ->schema($this->getProfileSchema())
                    ->extraAttributes([
                        'x-show' => "activeTab === 'profile'",
                        'x-cloak' => true,
                    ]),

                // Bookings Tab
                Group::make()
                    ->schema($this->getBookingsSchema())
                    ->extraAttributes([
                        'x-show' => "activeTab === 'bookings'",
                        'x-cloak' => true,
                    ]),

                // Deposit Tab
                Group::make()
                    ->schema($this->getDepositSchema())
                    ->extraAttributes([
                        'x-show' => "activeTab === 'deposit'",
                        'x-cloak' => true,
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Get form schema for the Profile tab.
     */
    private function getProfileSchema(): array
    {
        return [
            Section::make('Basic Information')
                ->description('Core information about your business that guests will see.')
                ->schema([
                    TextInput::make('name')
                        ->label('Business Name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('The name displayed on your booking page and in emails.'),

                    TextInput::make('slug')
                        ->label('URL Slug')
                        ->disabled()
                        ->helperText('Your URL identifier. Contact support to change this.'),

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
                ])
                ->columns(1),

            Section::make('Contact Information')
                ->description('How customers can reach you. This appears on your booking page.')
                ->schema([
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
                        ->url()
                        ->maxLength(500)
                        ->prefix('https://')
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
                        ->helperText('Your website (optional). You can enter just the domain name.'),
                ]),

            Section::make('Location')
                ->description('Your business address, shown on the booking page.')
                ->schema([
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
                                ->preload()
                                ->required()
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
                ]),

            Section::make('Timezone')
                ->description('All reservation times are shown in this timezone.')
                ->schema([
                    Select::make('timezone')
                        ->label('Timezone')
                        ->options($this->getTimezoneOptions())
                        ->searchable()
                        ->required()
                        ->helperText('Make sure this matches your physical location.'),
                ])
                ->columns(1),
        ];
    }

    /**
     * Get form schema for the Bookings tab.
     */
    private function getBookingsSchema(): array
    {
        return [
            Section::make('Online Booking')
                ->description('Enable or disable your public booking page.')
                ->schema([
                    Toggle::make('booking_enabled')
                        ->label('Enable Online Booking')
                        ->helperText('When enabled, guests can make reservations through your public booking page.')
                        ->live(),

                    TextInput::make('booking_public_slug')
                        ->label('Booking Page URL')
                        ->helperText('The unique link to your booking page. Share this with your guests.')
                        ->prefix(url('/book/'))
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('generate')
                                ->icon('heroicon-o-arrow-path')
                                ->tooltip('Generate new URL')
                                ->action(function ($set, $get) {
                                    $restaurant = CurrentRestaurant::get();
                                    if ($restaurant) {
                                        $set('booking_public_slug', Str::slug($restaurant->name) . '-' . Str::random(6));
                                    }
                                })
                        )
                        ->maxLength(100)
                        ->unique('restaurants', 'booking_public_slug', ignorable: CurrentRestaurant::get())
                        ->visible(fn ($get) => $get('booking_enabled')),

                    Placeholder::make('booking_url_preview')
                        ->label('Your Booking Link')
                        ->content(function ($get) {
                            $slug = $get('booking_public_slug');
                            if (! $slug) {
                                return new HtmlString('<span class="text-gray-400">Enter a URL above to see your booking link</span>');
                            }

                            $url = url("/book/{$slug}");

                            return new HtmlString(
                                '<a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline font-medium">' . $url . '</a>' .
                                '<button type="button" onclick="navigator.clipboard.writeText(\'' . $url . '\'); this.innerText=\'Copied!\'; setTimeout(() => this.innerText=\'Copy\', 2000);" ' .
                                'class="ml-3 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">Copy</button>'
                            );
                        })
                        ->visible(fn ($get) => $get('booking_enabled') && $get('booking_public_slug')),
                ])
                ->columns(1),

            Section::make('Party Size')
                ->description('How many guests can book online? Larger groups will be asked to contact you directly.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('booking_min_party_size')
                                ->label('Minimum Guests')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(1)
                                ->required()
                                ->suffix('guests')
                                ->helperText('Smallest party you accept online.')
                                ->live(onBlur: true),

                            TextInput::make('booking_max_party_size')
                                ->label('Maximum Guests')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(20)
                                ->required()
                                ->suffix('guests')
                                ->helperText('Largest party for online booking.')
                                ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                    if ((int) $value < $minPartySize) {
                                        $fail("Maximum guests must be at least {$minPartySize}.");
                                    }
                                }),
                        ]),
                ]),

            Section::make('Timing')
                ->description('Control when guests can book and how long reservations last.')
                ->schema([
                    Grid::make(3)
                        ->schema([
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
                        ]),
                ]),

            Section::make('Staff Notes')
                ->description('Private notes only visible to your team.')
                ->schema([
                    Textarea::make('booking_notes_internal')
                        ->label('Internal Notes')
                        ->helperText('Add any booking policies or instructions for your staff. Guests will not see this.')
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->columns(1)
                ->collapsed(),
        ];
    }

    /**
     * Get form schema for the Deposit tab.
     */
    private function getDepositSchema(): array
    {
        $restaurant = CurrentRestaurant::get();
        $currency = $restaurant?->booking_deposit_currency ?? 'EUR';
        $currencySymbol = self::CURRENCY_SYMBOLS[$currency] ?? $currency;

        return [
            Section::make('Deposit Settings')
                ->description('Request a deposit for larger groups to reduce no-shows. Deposits are not automatically charged – they are used for communication in emails and internal tracking.')
                ->schema([
                    Toggle::make('booking_deposit_enabled')
                        ->label('Enable Deposit Requests')
                        ->helperText('When enabled, larger parties will see deposit information on the booking page and in confirmation emails.')
                        ->live(),

                    Placeholder::make('deposit_example')
                        ->label('')
                        ->content(new HtmlString(
                            '<div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">' .
                            '<p class="text-sm text-amber-800 dark:text-amber-200">' .
                            '<strong>Example:</strong> You can request €10 per guest for parties of 4 or more. ' .
                            'A party of 6 would see a €60 deposit notice.' .
                            '</p></div>'
                        ))
                        ->visible(fn ($get) => $get('booking_deposit_enabled')),
                ])
                ->columns(1),

            Section::make('Deposit Rules')
                ->description('Configure when and how much deposit to request.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('booking_deposit_threshold_party_size')
                                ->label('Apply for Parties Of')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(4)
                                ->required()
                                ->suffix('or more guests')
                                ->helperText('Smaller groups won\'t see deposit requirements.'),

                            Select::make('booking_deposit_type')
                                ->label('Deposit Type')
                                ->options([
                                    'fixed_per_person' => 'Per guest (e.g., €10 × number of guests)',
                                    'fixed_per_reservation' => 'Fixed amount (e.g., €50 total)',
                                ])
                                ->default('fixed_per_person')
                                ->required()
                                ->helperText('How the deposit amount is calculated.'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('booking_deposit_amount')
                                ->label('Deposit Amount')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->default(10)
                                ->required()
                                ->prefix($currencySymbol)
                                ->helperText(fn ($get) => $get('booking_deposit_type') === 'fixed_per_person'
                                    ? 'Amount per guest.'
                                    : 'Total deposit amount.'),

                            Placeholder::make('currency_display')
                                ->label('Currency')
                                ->content(new HtmlString(
                                    '<div class="flex items-center gap-2 py-2">' .
                                    '<span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg font-medium">' .
                                    $currency . ' (' . $currencySymbol . ')' .
                                    '</span>' .
                                    '<span class="text-sm text-gray-500">Based on your country</span>' .
                                    '</div>'
                                ))
                                ->helperText('Currency is determined by your restaurant\'s location.'),
                        ]),
                ])
                ->visible(fn ($get) => $get('booking_deposit_enabled')),

            Section::make('Payment Instructions')
                ->description('Tell guests how to pay the deposit.')
                ->schema([
                    Textarea::make('booking_deposit_policy')
                        ->label('Deposit Policy & Instructions')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder("e.g., Please transfer the deposit to our bank account within 24 hours:\nBank: Example Bank\nIBAN: DE89 3704 0044 0532 0130 00\nReference: Your booking name and date")
                        ->helperText('This text appears in booking confirmation emails. Include payment details like bank account, PayPal, or other instructions.'),
                ])
                ->visible(fn ($get) => $get('booking_deposit_enabled'))
                ->columns(1),

            Section::make('')
                ->schema([
                    Placeholder::make('no_deposit_info')
                        ->label('')
                        ->content(new HtmlString(
                            '<div class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">' .
                            '<p class="text-sm text-gray-600 dark:text-gray-400">' .
                            'Deposits are currently disabled. Enable them above to request deposits for larger groups.' .
                            '</p></div>'
                        )),
                ])
                ->visible(fn ($get) => ! $get('booking_deposit_enabled')),
        ];
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

        // Extract settings fields for JSON column
        $settings = $restaurant->settings ?? [];
        $settings['tagline'] = $data['tagline'] ?? null;
        $settings['description'] = $data['description'] ?? null;
        $settings['phone'] = $data['phone'] ?? null;
        $settings['email'] = $data['email'] ?? null;
        $settings['website_url'] = $data['website_url'] ?? null;

        $restaurant->update([
            // Profile fields
            'name' => $data['name'],
            'cuisine_id' => $data['cuisine_id'] ?? null,
            'country_id' => $data['country_id'],
            'city_id' => $data['city_id'],
            'address_street' => $data['address_street'] ?? null,
            'address_district' => $data['address_district'] ?? null,
            'address_postal' => $data['address_postal'] ?? null,
            'timezone' => $data['timezone'],
            'settings' => $settings,

            // Booking fields
            'booking_enabled' => $data['booking_enabled'] ?? false,
            'booking_public_slug' => ($data['booking_enabled'] ?? false) ? ($data['booking_public_slug'] ?? null) : null,
            'booking_min_party_size' => $data['booking_min_party_size'] ?? 1,
            'booking_max_party_size' => $data['booking_max_party_size'] ?? 20,
            'booking_default_duration_minutes' => $data['booking_default_duration_minutes'] ?? 90,
            'booking_min_lead_time_minutes' => $data['booking_min_lead_time_minutes'] ?? 60,
            'booking_max_lead_time_days' => $data['booking_max_lead_time_days'] ?? 30,
            'booking_notes_internal' => $data['booking_notes_internal'] ?? null,

            // Deposit fields
            'booking_deposit_enabled' => $data['booking_deposit_enabled'] ?? false,
            'booking_deposit_threshold_party_size' => $data['booking_deposit_threshold_party_size'] ?? 4,
            'booking_deposit_type' => $data['booking_deposit_type'] ?? 'fixed_per_person',
            'booking_deposit_amount' => $data['booking_deposit_amount'] ?? 10,
            'booking_deposit_policy' => $data['booking_deposit_policy'] ?? null,
        ]);

        Notification::make()
            ->title('Settings Saved')
            ->body('Your settings have been updated successfully.')
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
        return [
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
    }
}
