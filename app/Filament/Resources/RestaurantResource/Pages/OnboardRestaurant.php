<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Enums\GlobalRole;
use App\Enums\RestaurantRole;
use App\Filament\Resources\RestaurantResource;
use App\Models\City;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
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
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Admin onboarding wizard for creating a new restaurant with its owner.
 *
 * This page allows platform admins to create a restaurant along with either
 * linking an existing user or creating a new owner user in one transaction.
 */
class OnboardRestaurant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = RestaurantResource::class;

    protected static string $view = 'filament.resources.restaurant-resource.pages.onboard-restaurant';

    protected static ?string $title = 'Onboard New Restaurant';

    protected static ?string $navigationLabel = 'Onboard Restaurant';

    public ?array $data = [];

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isPlatformAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            // Restaurant defaults
            'booking_enabled' => true,
            'booking_public_slug' => '',
            'booking_min_party_size' => 2,
            'booking_max_party_size' => 8,
            'booking_default_duration_minutes' => 90,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
            'timezone' => config('app.timezone', 'Europe/Berlin'),
            // Deposit defaults
            'booking_deposit_enabled' => false,
            'booking_deposit_threshold_party_size' => 4,
            'booking_deposit_type' => 'fixed_per_person',
            'booking_deposit_amount' => 0,
            'booking_deposit_currency' => 'EUR',
            // Owner defaults
            'owner_mode' => 'new_user',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Restaurant Basics')
                    ->description('Core information about the restaurant.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Restaurant Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->maxLength(255)
                            ->unique('restaurants', 'slug')
                            ->helperText('Leave blank to auto-generate from name.'),

                        Select::make('cuisine_id')
                            ->label('Main Cuisine')
                            ->options(fn () => Cuisine::query()->ordered()->pluck('name_en', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select the main cuisine')
                            ->helperText('Choose the main cuisine that guests will see on the public profile.'),

                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(150)
                            ->helperText('A short phrase describing the restaurant.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Public Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('website_url')
                            ->label('Website URL')
                            ->maxLength(500)
                            ->prefix('https://')
                            ->placeholder('meraki.bar')
                            ->helperText('Your website (optional). You can enter just the domain name.'),
                    ])
                    ->columns(2),

                Section::make('Location & Timezone')
                    ->description('Physical location and timezone settings.')
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
                            ->required(),

                        TextInput::make('address_street')
                            ->label('Street Address')
                            ->maxLength(255),

                        TextInput::make('address_district')
                            ->label('District / Neighborhood')
                            ->maxLength(255),

                        TextInput::make('address_postal')
                            ->label('Postal Code')
                            ->maxLength(20),

                        Select::make('timezone')
                            ->label('Timezone')
                            ->options(fn () => array_combine(
                                \DateTimeZone::listIdentifiers(),
                                \DateTimeZone::listIdentifiers()
                            ))
                            ->searchable()
                            ->required()
                            ->default(config('app.timezone', 'Europe/Berlin')),
                    ])
                    ->columns(2),

                Section::make('Booking Defaults')
                    ->description('Initial booking configuration. Can be adjusted later.')
                    ->schema([
                        Toggle::make('booking_enabled')
                            ->label('Enable Public Booking')
                            ->helperText('When enabled, customers can make reservations online.')
                            ->default(true)
                            ->live(),

                        TextInput::make('booking_public_slug')
                            ->label('Public Booking URL Slug')
                            ->helperText('Used for /book/{slug}. Leave empty to auto-generate from the restaurant name.')
                            ->maxLength(100)
                            ->visible(fn (Get $get) => $get('booking_enabled')),

                        TextInput::make('booking_min_party_size')
                            ->label('Minimum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(2)
                            ->required()
                            ->suffix('guests')
                            ->live(onBlur: true),

                        TextInput::make('booking_max_party_size')
                            ->label('Maximum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(8)
                            ->required()
                            ->suffix('guests')
                            ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                if ((int) $value < $minPartySize) {
                                    $fail("The Maximum Party Size must be at least {$minPartySize} (the minimum party size).");
                                }
                            }),

                        TextInput::make('booking_default_duration_minutes')
                            ->label('Default Reservation Duration')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(90)
                            ->required()
                            ->suffix('minutes'),

                        TextInput::make('booking_min_lead_time_minutes')
                            ->label('Minimum Lead Time')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440)
                            ->default(60)
                            ->required()
                            ->suffix('minutes')
                            ->helperText('How far in advance a reservation must be made.'),

                        TextInput::make('booking_max_lead_time_days')
                            ->label('Maximum Lead Time')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(30)
                            ->required()
                            ->suffix('days')
                            ->helperText('How far into the future reservations can be made.'),

                        Textarea::make('booking_notes_internal')
                            ->label('Internal Booking Notes')
                            ->rows(2)
                            ->maxLength(1000)
                            ->helperText('Private notes for staff about booking policies.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Deposit Settings')
                    ->description('Require a deposit for larger groups (optional, can be configured later).')
                    ->schema([
                        Toggle::make('booking_deposit_enabled')
                            ->label('Enable deposit requirement')
                            ->helperText('When enabled, larger groups will be required to pay a deposit.')
                            ->default(false)
                            ->live(),

                        TextInput::make('booking_deposit_threshold_party_size')
                            ->label('Deposit threshold (party size)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(4)
                            ->suffix('guests')
                            ->helperText('Deposit is required for this party size and above.')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled'))
                            ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                if ((int) $value < $minPartySize) {
                                    $fail("The deposit threshold must be at least {$minPartySize} (the minimum party size).");
                                }
                            }),

                        Select::make('booking_deposit_type')
                            ->label('Deposit type')
                            ->options([
                                'fixed_per_person' => 'Per person',
                                'fixed_per_reservation' => 'Per reservation',
                            ])
                            ->default('fixed_per_person')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        TextInput::make('booking_deposit_amount')
                            ->label('Deposit amount')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->default(0)
                            ->prefix('€')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        TextInput::make('booking_deposit_currency')
                            ->label('Currency')
                            ->maxLength(3)
                            ->default('EUR')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled')),

                        Textarea::make('booking_deposit_policy')
                            ->label('Deposit policy')
                            ->rows(3)
                            ->maxLength(2000)
                            ->helperText('Explain your deposit / cancellation policy. This is shown to guests when a deposit is required.')
                            ->visible(fn (Get $get) => $get('booking_deposit_enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Media')
                    ->description('Logo and cover images for the booking page (optional but recommended).')
                    ->schema([
                        FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->imagePreviewHeight('80')
                            ->disk('public')
                            ->directory('restaurants/logos')
                            ->maxSize(2048)
                            ->helperText('Square image, at least 512×512 px. PNG, JPG, or SVG. Max 2MB.'),

                        FileUpload::make('cover_image_url')
                            ->label('Cover Image')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->disk('public')
                            ->directory('restaurants/covers')
                            ->maxSize(5120)
                            ->helperText('Landscape image, ideally 1920×1080 px (16:9 ratio). PNG or JPG. Max 5MB.'),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Owner User')
                    ->description('Link an existing user or create a new owner account.')
                    ->schema([
                        Radio::make('owner_mode')
                            ->label('Owner Type')
                            ->options([
                                'existing_user' => 'Link existing user',
                                'new_user' => 'Create new user',
                            ])
                            ->default('new_user')
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('owner_user_id')
                            ->label('Select Existing User')
                            ->options(function () {
                                return User::query()
                                    ->where('global_role', GlobalRole::User)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $user) => [
                                        $user->id => "{$user->name} ({$user->email})",
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('owner_mode') === 'existing_user')
                            ->required(fn (Get $get) => $get('owner_mode') === 'existing_user')
                            ->helperText('Select a user to make them the owner of this restaurant.'),

                        TextInput::make('owner_name')
                            ->label('Owner Name')
                            ->visible(fn (Get $get) => $get('owner_mode') === 'new_user')
                            ->required(fn (Get $get) => $get('owner_mode') === 'new_user')
                            ->maxLength(255),

                        TextInput::make('owner_email')
                            ->label('Owner Email')
                            ->email()
                            ->visible(fn (Get $get) => $get('owner_mode') === 'new_user')
                            ->required(fn (Get $get) => $get('owner_mode') === 'new_user')
                            ->unique('users', 'email')
                            ->maxLength(255)
                            ->helperText('A random password will be generated. Send login details separately.'),

                        TextInput::make('owner_phone')
                            ->label('Owner Phone')
                            ->tel()
                            ->visible(fn (Get $get) => $get('owner_mode') === 'new_user')
                            ->maxLength(50),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        try {
            $restaurant = DB::transaction(function () use ($data) {
                // 1. Normalize website URL
                $websiteUrl = null;
                if (! empty($data['website_url'])) {
                    $trimmed = trim($data['website_url']);
                    if (! empty($trimmed)) {
                        if (! preg_match('/^https?:\/\//i', $trimmed)) {
                            $websiteUrl = 'https://' . $trimmed;
                        } else {
                            $websiteUrl = $trimmed;
                        }
                    }
                }

                // 2. Create the restaurant
                $settings = array_filter([
                    'tagline' => $data['tagline'] ?? null,
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'website_url' => $websiteUrl,
                ]);

                $restaurant = Restaurant::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?: Str::slug($data['name']),
                    'cuisine_id' => $data['cuisine_id'] ?? null,
                    'country_id' => $data['country_id'],
                    'city_id' => $data['city_id'],
                    'address_street' => $data['address_street'] ?? null,
                    'address_district' => $data['address_district'] ?? null,
                    'address_postal' => $data['address_postal'] ?? null,
                    'timezone' => $data['timezone'],
                    'settings' => ! empty($settings) ? $settings : null,
                    'is_active' => true,
                    // Media
                    'logo_url' => $data['logo_url'] ?? null,
                    'cover_image_url' => $data['cover_image_url'] ?? null,
                    // Booking settings
                    'booking_enabled' => $data['booking_enabled'] ?? false,
                    'booking_public_slug' => $data['booking_enabled']
                        ? (! empty($data['booking_public_slug']) ? $data['booking_public_slug'] : Str::slug($data['name']) . '-' . Str::random(6))
                        : null,
                    'booking_min_party_size' => $data['booking_min_party_size'] ?? 2,
                    'booking_max_party_size' => $data['booking_max_party_size'] ?? 8,
                    'booking_default_duration_minutes' => $data['booking_default_duration_minutes'] ?? 90,
                    'booking_min_lead_time_minutes' => $data['booking_min_lead_time_minutes'] ?? 60,
                    'booking_max_lead_time_days' => $data['booking_max_lead_time_days'] ?? 30,
                    'booking_notes_internal' => $data['booking_notes_internal'] ?? null,
                    // Deposit settings
                    'booking_deposit_enabled' => $data['booking_deposit_enabled'] ?? false,
                    'booking_deposit_threshold_party_size' => $data['booking_deposit_threshold_party_size'] ?? 4,
                    'booking_deposit_type' => $data['booking_deposit_type'] ?? 'fixed_per_person',
                    'booking_deposit_amount' => $data['booking_deposit_amount'] ?? 0.00,
                    'booking_deposit_currency' => $data['booking_deposit_currency'] ?? 'EUR',
                    'booking_deposit_policy' => $data['booking_deposit_policy'] ?? null,
                ]);

                // 3. Resolve or create the owner user
                if ($data['owner_mode'] === 'existing_user') {
                    $ownerUser = User::findOrFail($data['owner_user_id']);
                } else {
                    // Create new user with random password
                    $ownerUser = User::create([
                        'name' => $data['owner_name'],
                        'email' => $data['owner_email'],
                        'password' => Str::random(32), // Will be hashed by the model's cast
                        'global_role' => GlobalRole::User,
                    ]);
                }

                // 4. Create the RestaurantUser pivot
                RestaurantUser::create([
                    'restaurant_id' => $restaurant->id,
                    'user_id' => $ownerUser->id,
                    'role' => RestaurantRole::Owner,
                    'is_active' => true,
                ]);

                return $restaurant;
            });

            $ownerInfo = $data['owner_mode'] === 'existing_user'
                ? 'linked to existing user'
                : "created new user: {$data['owner_email']}";

            Notification::make()
                ->title('Restaurant Onboarded Successfully')
                ->body("Restaurant \"{$restaurant->name}\" has been created and {$ownerInfo}. You can now edit additional details or configure opening hours.")
                ->success()
                ->send();

            Log::info('Restaurant onboarded via admin wizard', [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'owner_mode' => $data['owner_mode'],
                'created_by' => Auth::id(),
            ]);

            $this->redirect(RestaurantResource::getUrl('edit', ['record' => $restaurant]));

        } catch (\Throwable $e) {
            Log::error('Restaurant onboarding failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            Notification::make()
                ->title('Onboarding Failed')
                ->body('An error occurred while creating the restaurant. Please check the logs.')
                ->danger()
                ->send();
        }
    }

}
