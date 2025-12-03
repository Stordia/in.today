<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Booking settings
            [
                'key' => 'booking.default_notification_email',
                'value' => null,
                'type' => 'string',
                'group' => 'booking',
                'description' => 'Default email address for booking notifications (fallback when restaurant has no email)',
            ],
            [
                'key' => 'booking.send_customer_confirmation',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'booking',
                'description' => 'Whether to send confirmation emails to customers after booking',
            ],
            [
                'key' => 'booking.send_restaurant_notification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'booking',
                'description' => 'Whether to send notification emails to restaurants for new bookings',
            ],

            // Email settings
            [
                'key' => 'email.from_address',
                'value' => null,
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default FROM email address (falls back to config mail.from.address)',
            ],
            [
                'key' => 'email.from_name',
                'value' => null,
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default FROM name (falls back to config mail.from.name)',
            ],
            [
                'key' => 'email.reply_to_address',
                'value' => null,
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default reply-to email address',
            ],

            // Affiliate settings
            [
                'key' => 'affiliate.default_commission_rate',
                'value' => '10',
                'type' => 'integer',
                'group' => 'affiliate',
                'description' => 'Default commission rate for affiliates (percentage)',
            ],
            [
                'key' => 'affiliate.payout_threshold',
                'value' => '50',
                'type' => 'integer',
                'group' => 'affiliate',
                'description' => 'Minimum balance required for affiliate payout (in euros)',
            ],
            [
                'key' => 'affiliate.cookie_lifetime_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'affiliate',
                'description' => 'Number of days the affiliate cookie stays valid',
            ],

            // Technical settings
            [
                'key' => 'technical.maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'technical',
                'description' => 'Enable maintenance mode for public-facing pages',
            ],
            [
                'key' => 'technical.log_level',
                'value' => 'info',
                'type' => 'string',
                'group' => 'technical',
                'description' => 'Minimum log level to record',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                    'is_encrypted' => false,
                ]
            );
        }
    }
}
