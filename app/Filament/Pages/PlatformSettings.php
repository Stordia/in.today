<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\AppSettings;
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

    public static function getNavigationLabel(): string
    {
        return 'Platform Settings';
    }

    public function getTitle(): string
    {
        return 'Platform Settings';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isPlatformAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            // Email settings
            'email_from_address' => AppSettings::get(
                'email.from_address',
                config('mail.from.address', 'noreply@in.today')
            ),
            'email_from_name' => AppSettings::get(
                'email.from_name',
                config('mail.from.name', config('app.name', 'in.today'))
            ),
            'email_reply_to_address' => AppSettings::get(
                'email.reply_to_address',
                AppSettings::get('email.from_address', config('mail.from.address'))
            ),

            // Booking settings
            'booking_send_customer_confirmation' => AppSettings::get('booking.send_customer_confirmation', true),
            'booking_send_restaurant_notification' => AppSettings::get('booking.send_restaurant_notification', true),
            'booking_default_notification_email' => AppSettings::get(
                'booking.default_notification_email',
                config('services.bookings.notification_email')
            ),

            // Affiliate settings
            'affiliate_default_commission_rate' => AppSettings::get('affiliate.default_commission_rate', 10),
            'affiliate_payout_threshold' => AppSettings::get('affiliate.payout_threshold', 50),
            'affiliate_cookie_lifetime_days' => AppSettings::get('affiliate.cookie_lifetime_days', 30),

            // Technical settings
            'technical_maintenance_mode' => AppSettings::get('technical.maintenance_mode', false),
            'technical_log_level' => AppSettings::get('technical.log_level', 'info'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Email')
                    ->description('Global email configuration used across the platform.')
                    ->schema([
                        TextInput::make('email_from_address')
                            ->label('From address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email_from_name')
                            ->label('From name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email_reply_to_address')
                            ->label('Reply-to address')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Bookings & Reservations')
                    ->description('Defaults for the reservation system and public booking widget.')
                    ->schema([
                        Toggle::make('booking_send_customer_confirmation')
                            ->label('Send confirmation to customer')
                            ->helperText('Send an email confirmation to customers after they make a booking.'),
                        Toggle::make('booking_send_restaurant_notification')
                            ->label('Send notification to restaurant')
                            ->helperText('Send an email notification to restaurants for new bookings.'),
                        TextInput::make('booking_default_notification_email')
                            ->label('Default restaurant notification email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Used when a restaurant has no specific notification email configured.'),
                    ])
                    ->columns(2),

                Section::make('Affiliates')
                    ->description('Defaults for the affiliate program.')
                    ->schema([
                        TextInput::make('affiliate_default_commission_rate')
                            ->label('Default commission rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->suffix('%')
                            ->required(),
                        TextInput::make('affiliate_payout_threshold')
                            ->label('Payout threshold (EUR)')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix('€')
                            ->required(),
                        TextInput::make('affiliate_cookie_lifetime_days')
                            ->label('Cookie lifetime (days)')
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->suffix('days')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Technical')
                    ->description('Internal technical flags. Be careful when changing these.')
                    ->schema([
                        Toggle::make('technical_maintenance_mode')
                            ->label('Logical maintenance flag')
                            ->helperText('A logical flag for maintenance mode. Does NOT call artisan down/up.'),
                        Select::make('technical_log_level')
                            ->label('Log level')
                            ->options([
                                'debug' => 'Debug',
                                'info' => 'Info',
                                'warning' => 'Warning',
                                'error' => 'Error',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Email settings
        AppSettings::set(
            'email.from_address',
            $data['email_from_address'],
            'email',
            'Default FROM address for system emails.'
        );
        AppSettings::set(
            'email.from_name',
            $data['email_from_name'],
            'email',
            'Default FROM name for system emails.'
        );
        AppSettings::set(
            'email.reply_to_address',
            $data['email_reply_to_address'],
            'email',
            'Default reply-to address for system emails.'
        );

        // Booking settings
        AppSettings::set(
            'booking.send_customer_confirmation',
            (bool) $data['booking_send_customer_confirmation'],
            'booking',
            'Whether to send confirmation emails to customers after booking.'
        );
        AppSettings::set(
            'booking.send_restaurant_notification',
            (bool) $data['booking_send_restaurant_notification'],
            'booking',
            'Whether to send notification emails to restaurants for new bookings.'
        );
        AppSettings::set(
            'booking.default_notification_email',
            $data['booking_default_notification_email'],
            'booking',
            'Default email for booking notifications when restaurant has no email.'
        );

        // Affiliate settings
        AppSettings::set(
            'affiliate.default_commission_rate',
            (int) $data['affiliate_default_commission_rate'],
            'affiliate',
            'Default commission rate for new affiliates (percentage).'
        );
        AppSettings::set(
            'affiliate.payout_threshold',
            (int) $data['affiliate_payout_threshold'],
            'affiliate',
            'Minimum balance required for affiliate payout (in euros).'
        );
        AppSettings::set(
            'affiliate.cookie_lifetime_days',
            (int) $data['affiliate_cookie_lifetime_days'],
            'affiliate',
            'Number of days the affiliate cookie stays valid.'
        );

        // Technical settings
        AppSettings::set(
            'technical.maintenance_mode',
            (bool) $data['technical_maintenance_mode'],
            'technical',
            'Logical maintenance flag (does not call artisan down/up).'
        );
        AppSettings::set(
            'technical.log_level',
            $data['technical_log_level'],
            'technical',
            'Minimum log level to record.'
        );

        Notification::make()
            ->title('Settings updated')
            ->body('Your platform configuration has been saved.')
            ->success()
            ->send();
    }
}
