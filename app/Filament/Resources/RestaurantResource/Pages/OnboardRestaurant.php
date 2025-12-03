<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Enums\GlobalRole;
use App\Enums\RestaurantRole;
use App\Filament\Resources\RestaurantResource;
use App\Models\City;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
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
            'booking_min_party_size' => 1,
            'booking_max_party_size' => 12,
            'booking_default_duration_minutes' => 90,
            'booking_min_lead_time_minutes' => 60,
            'booking_max_lead_time_days' => 30,
            'timezone' => config('app.timezone', 'Europe/Berlin'),
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

                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(150)
                            ->helperText('A short phrase describing the restaurant.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000),

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
                            ->url()
                            ->maxLength(500),
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
                            ->default(true),

                        TextInput::make('booking_min_party_size')
                            ->label('Minimum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(1)
                            ->required()
                            ->suffix('guests')
                            ->live(onBlur: true),

                        TextInput::make('booking_max_party_size')
                            ->label('Maximum Party Size')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(12)
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
                // 1. Create the restaurant
                $settings = array_filter([
                    'tagline' => $data['tagline'] ?? null,
                    'description' => $data['description'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'website_url' => $data['website_url'] ?? null,
                ]);

                $restaurant = Restaurant::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?: Str::slug($data['name']),
                    'country_id' => $data['country_id'],
                    'city_id' => $data['city_id'],
                    'address_street' => $data['address_street'] ?? null,
                    'address_district' => $data['address_district'] ?? null,
                    'address_postal' => $data['address_postal'] ?? null,
                    'timezone' => $data['timezone'],
                    'settings' => ! empty($settings) ? $settings : null,
                    'is_active' => true,
                    // Booking settings
                    'booking_enabled' => $data['booking_enabled'] ?? false,
                    'booking_public_slug' => $data['booking_enabled'] ? Str::slug($data['name']) . '-' . Str::random(6) : null,
                    'booking_min_party_size' => $data['booking_min_party_size'] ?? 1,
                    'booking_max_party_size' => $data['booking_max_party_size'] ?? 12,
                    'booking_default_duration_minutes' => $data['booking_default_duration_minutes'] ?? 90,
                    'booking_min_lead_time_minutes' => $data['booking_min_lead_time_minutes'] ?? 60,
                    'booking_max_lead_time_days' => $data['booking_max_lead_time_days'] ?? 30,
                    'booking_notes_internal' => $data['booking_notes_internal'] ?? null,
                ]);

                // 2. Resolve or create the owner user
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

                // 3. Create the RestaurantUser pivot
                RestaurantUser::create([
                    'restaurant_id' => $restaurant->id,
                    'user_id' => $ownerUser->id,
                    'name' => $ownerUser->name,
                    'email' => $ownerUser->email,
                    'role' => RestaurantRole::Owner,
                    'is_active' => true,
                ]);

                return $restaurant;
            });

            $ownerInfo = $data['owner_mode'] === 'existing_user'
                ? 'linked to existing user'
                : "created new user: {$data['owner_email']}";

            Notification::make()
                ->title('Restaurant Created Successfully')
                ->body("Restaurant \"{$restaurant->name}\" has been created and {$ownerInfo}.")
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
