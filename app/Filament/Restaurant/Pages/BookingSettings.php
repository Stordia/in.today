<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Booking Settings page for the Business panel.
 *
 * @deprecated Use RestaurantSettings instead. This page now redirects to Settings → Bookings tab.
 */
class BookingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Booking Settings';

    protected static ?string $title = 'Booking Settings';

    protected static string $view = 'filament.restaurant.pages.booking-settings';

    // Hide from navigation - use Settings instead
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            $this->data = [];

            return;
        }

        $this->form->fill([
            'booking_enabled' => $restaurant->booking_enabled,
            'booking_public_slug' => $restaurant->booking_public_slug,
            'booking_min_party_size' => $restaurant->booking_min_party_size,
            'booking_max_party_size' => $restaurant->booking_max_party_size,
            'booking_default_duration_minutes' => $restaurant->booking_default_duration_minutes,
            'booking_min_lead_time_minutes' => $restaurant->booking_min_lead_time_minutes,
            'booking_max_lead_time_days' => $restaurant->booking_max_lead_time_days,
            'booking_notes_internal' => $restaurant->booking_notes_internal,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return 'Booking Settings';
    }

    public function getSubheading(): ?string
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'Please select a restaurant to configure booking settings.';
        }

        return "Configure public booking options for {$restaurant->name}";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Public Booking')
                    ->description('Control whether customers can make reservations online.')
                    ->schema([
                        Toggle::make('booking_enabled')
                            ->label('Enable Public Booking')
                            ->helperText('When enabled, customers can make reservations through your public booking page.')
                            ->live(),

                        TextInput::make('booking_public_slug')
                            ->label('Booking URL Slug')
                            ->helperText('The unique identifier for your public booking page.')
                            ->prefix(url('/book/'))
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function ($set, $get) {
                                        $restaurant = CurrentRestaurant::get();
                                        if ($restaurant) {
                                            $set('booking_public_slug', Str::slug($restaurant->name).'-'.Str::random(6));
                                        }
                                    })
                            )
                            ->maxLength(100)
                            ->unique('restaurants', 'booking_public_slug', ignorable: CurrentRestaurant::get())
                            ->visible(fn ($get) => $get('booking_enabled')),

                        Placeholder::make('booking_url_preview')
                            ->label('Booking URL')
                            ->content(function ($get) {
                                $slug = $get('booking_public_slug');
                                if (! $slug) {
                                    return new HtmlString('<span class="text-gray-400">Enter a slug to see the booking URL</span>');
                                }

                                $url = url("/book/{$slug}");

                                return new HtmlString("<a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">{$url}</a>");
                            })
                            ->visible(fn ($get) => $get('booking_enabled') && $get('booking_public_slug')),
                    ])
                    ->columns(1),

                Section::make('Party Size Limits')
                    ->description('Set the minimum and maximum number of guests per reservation.')
                    ->schema([
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
                            ->default(20)
                            ->required()
                            ->suffix('guests')
                            ->helperText('For larger parties, guests will be prompted to contact you directly.')
                            ->rule(fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $minPartySize = max(1, (int) ($get('booking_min_party_size') ?? 1));
                                if ((int) $value < $minPartySize) {
                                    $fail("The Maximum Party Size must be at least {$minPartySize} (the minimum party size).");
                                }
                            }),
                    ])
                    ->columns(2),

                Section::make('Timing')
                    ->description('Configure reservation duration and booking windows.')
                    ->schema([
                        TextInput::make('booking_default_duration_minutes')
                            ->label('Default Reservation Duration')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(90)
                            ->required()
                            ->suffix('minutes')
                            ->helperText('How long each reservation blocks the table.'),

                        TextInput::make('booking_min_lead_time_minutes')
                            ->label('Minimum Lead Time')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440)
                            ->default(60)
                            ->required()
                            ->suffix('minutes')
                            ->helperText('How far in advance a reservation must be made. Set to 0 for same-day bookings.'),

                        TextInput::make('booking_max_lead_time_days')
                            ->label('Maximum Lead Time')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(30)
                            ->required()
                            ->suffix('days')
                            ->helperText('How far into the future reservations can be made.'),
                    ])
                    ->columns(3),

                Section::make('Internal Notes')
                    ->description('Private notes visible only to staff.')
                    ->schema([
                        Textarea::make('booking_notes_internal')
                            ->label('Internal Booking Notes')
                            ->helperText('Notes for staff about booking policies, special instructions, etc. Not visible to customers.')
                            ->rows(3)
                            ->maxLength(1000),
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

        $restaurant->update([
            'booking_enabled' => $data['booking_enabled'] ?? false,
            'booking_public_slug' => $data['booking_enabled'] ? ($data['booking_public_slug'] ?? null) : null,
            'booking_min_party_size' => $data['booking_min_party_size'] ?? 1,
            'booking_max_party_size' => $data['booking_max_party_size'] ?? 20,
            'booking_default_duration_minutes' => $data['booking_default_duration_minutes'] ?? 90,
            'booking_min_lead_time_minutes' => $data['booking_min_lead_time_minutes'] ?? 60,
            'booking_max_lead_time_days' => $data['booking_max_lead_time_days'] ?? 30,
            'booking_notes_internal' => $data['booking_notes_internal'] ?? null,
        ]);

        Notification::make()
            ->title('Settings Saved')
            ->body('Booking settings have been updated successfully.')
            ->success()
            ->send();
    }

    public function hasRestaurant(): bool
    {
        return CurrentRestaurant::get() !== null;
    }
}
