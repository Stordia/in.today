<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\AppSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PlatformSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.platform-settings';

    public ?array $data = [];

    public string $activeTab = 'email';

    public static function getNavigationLabel(): string
    {
        return 'Platform Settings';
    }

    public function getTitle(): string
    {
        return 'Platform Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Global configuration for email, bookings, affiliates and technical flags.';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isPlatformAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'email' => [
                'from_address' => AppSettings::get(
                    'email.from_address',
                    config('mail.from.address', 'noreply@in.today')
                ),
                'from_name' => AppSettings::get(
                    'email.from_name',
                    config('mail.from.name', config('app.name', 'in.today'))
                ),
                'reply_to_address' => AppSettings::get(
                    'email.reply_to_address',
                    config('mail.from.address', '')
                ),
            ],
            'booking' => [
                'send_customer_confirmation' => (bool) AppSettings::get('booking.send_customer_confirmation', true),
                'send_restaurant_notification' => (bool) AppSettings::get('booking.send_restaurant_notification', true),
                'default_notification_email' => AppSettings::get(
                    'booking.default_notification_email',
                    config('services.bookings.notification_email', '')
                ),
            ],
            'affiliate' => [
                'default_commission_rate' => (float) AppSettings::get('affiliate.default_commission_rate', 10),
                'payout_threshold' => (float) AppSettings::get('affiliate.payout_threshold', 50),
                'cookie_lifetime_days' => (int) AppSettings::get('affiliate.cookie_lifetime_days', 30),
            ],
            'technical' => [
                'maintenance_mode' => (bool) AppSettings::get('technical.maintenance_mode', false),
                'log_level' => AppSettings::get('technical.log_level', 'info'),
            ],
        ]);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Email Section - always rendered but shown/hidden via Alpine in Blade
                Group::make()
                    ->schema([
                        Section::make('Email')
                            ->description('These are the global defaults used by transactional emails (bookings, CRM, affiliates). Individual modules can override if needed.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('email.from_address')
                                            ->label('From address')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('The sender email address for all system emails.'),
                                        TextInput::make('email.from_name')
                                            ->label('From name')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('The sender name shown in email clients.'),
                                    ]),
                                TextInput::make('email.reply_to_address')
                                    ->label('Reply-to address')
                                    ->email()
                                    ->maxLength(255)
                                    ->helperText('Where replies to system emails will be sent.'),
                            ]),
                    ])
                    ->extraAttributes([
                        'x-show' => "activeTab === 'email'",
                        'x-cloak' => true,
                    ]),

                // Bookings Section
                Group::make()
                    ->schema([
                        Section::make('Bookings & Reservations')
                            ->description("These are platform defaults for the reservation system and the public booking widget. They're used as initial defaults when a new restaurant is created, and as fallback when a restaurant has no specific booking settings configured.")
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('booking.send_customer_confirmation')
                                            ->label('Send confirmation to customer')
                                            ->helperText('Send an email confirmation to customers after they make a booking.'),
                                        Toggle::make('booking.send_restaurant_notification')
                                            ->label('Send notification to restaurant')
                                            ->helperText('Send an email notification to restaurants for new bookings.'),
                                    ]),
                                TextInput::make('booking.default_notification_email')
                                    ->label('Default restaurant notification email')
                                    ->email()
                                    ->maxLength(255)
                                    ->helperText('Used when a restaurant has no specific notification email configured.'),
                            ]),
                    ])
                    ->extraAttributes([
                        'x-show' => "activeTab === 'bookings'",
                        'x-cloak' => true,
                    ]),

                // Affiliates Section
                Group::make()
                    ->schema([
                        Section::make('Affiliates')
                            ->description('Used as defaults when creating new affiliates. The commission rate is also used by the AffiliateConversion approval logic when no specific rate is set on the affiliate.')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('affiliate.default_commission_rate')
                                            ->label('Default commission rate (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.01)
                                            ->suffix('%')
                                            ->required()
                                            ->helperText('Default commission percentage for new affiliates.'),
                                        TextInput::make('affiliate.payout_threshold')
                                            ->label('Payout threshold (EUR)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix('€')
                                            ->required()
                                            ->helperText('Minimum balance required before an affiliate can request payout.'),
                                        TextInput::make('affiliate.cookie_lifetime_days')
                                            ->label('Cookie lifetime (days)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->step(1)
                                            ->suffix('days')
                                            ->required()
                                            ->helperText('How long the affiliate tracking cookie remains valid.'),
                                    ]),
                            ]),
                    ])
                    ->extraAttributes([
                        'x-show' => "activeTab === 'affiliates'",
                        'x-cloak' => true,
                    ]),

                // Technical Section
                Group::make()
                    ->schema([
                        Section::make('Technical')
                            ->description('Internal technical flags. Be careful when changing these settings in production.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('technical.maintenance_mode')
                                            ->label('Logical maintenance flag')
                                            ->helperText('A logical flag for maintenance mode. Does NOT call artisan down/up. Use this in middleware to show a maintenance page for non-admins.'),
                                        Select::make('technical.log_level')
                                            ->label('Log level')
                                            ->options([
                                                'debug' => 'Debug',
                                                'info' => 'Info',
                                                'warning' => 'Warning',
                                                'error' => 'Error',
                                            ])
                                            ->required()
                                            ->helperText('Controls the runtime logging level. Keep on "info" in production.'),
                                    ]),
                            ]),
                    ])
                    ->extraAttributes([
                        'x-show' => "activeTab === 'technical'",
                        'x-cloak' => true,
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Email settings - use data_get to safely access nested values
        AppSettings::set(
            'email.from_address',
            (string) data_get($data, 'email.from_address', ''),
            'email',
            'Default FROM address for system emails.'
        );
        AppSettings::set(
            'email.from_name',
            (string) data_get($data, 'email.from_name', ''),
            'email',
            'Default FROM name for system emails.'
        );
        AppSettings::set(
            'email.reply_to_address',
            (string) data_get($data, 'email.reply_to_address', ''),
            'email',
            'Default reply-to address for system emails.'
        );

        // Booking settings
        AppSettings::set(
            'booking.send_customer_confirmation',
            (bool) data_get($data, 'booking.send_customer_confirmation', true),
            'booking',
            'Whether to send confirmation emails to customers after booking.'
        );
        AppSettings::set(
            'booking.send_restaurant_notification',
            (bool) data_get($data, 'booking.send_restaurant_notification', true),
            'booking',
            'Whether to send notification emails to restaurants for new bookings.'
        );
        AppSettings::set(
            'booking.default_notification_email',
            (string) data_get($data, 'booking.default_notification_email', ''),
            'booking',
            'Default email for booking notifications when restaurant has no email.'
        );

        // Affiliate settings
        AppSettings::set(
            'affiliate.default_commission_rate',
            (float) data_get($data, 'affiliate.default_commission_rate', 10),
            'affiliate',
            'Default commission rate for new affiliates (percentage).'
        );
        AppSettings::set(
            'affiliate.payout_threshold',
            (float) data_get($data, 'affiliate.payout_threshold', 50),
            'affiliate',
            'Minimum balance required for affiliate payout (in euros).'
        );
        AppSettings::set(
            'affiliate.cookie_lifetime_days',
            (int) data_get($data, 'affiliate.cookie_lifetime_days', 30),
            'affiliate',
            'Number of days the affiliate cookie stays valid.'
        );

        // Technical settings
        AppSettings::set(
            'technical.maintenance_mode',
            (bool) data_get($data, 'technical.maintenance_mode', false),
            'technical',
            'Logical maintenance flag (does not call artisan down/up).'
        );
        AppSettings::set(
            'technical.log_level',
            (string) data_get($data, 'technical.log_level', 'info'),
            'technical',
            'Minimum log level to record.'
        );

        Notification::make()
            ->title('Settings saved')
            ->body('Your platform configuration has been saved successfully.')
            ->success()
            ->send();
    }
}
