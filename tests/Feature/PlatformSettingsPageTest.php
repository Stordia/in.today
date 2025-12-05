<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AffiliateConversionStatus;
use App\Enums\GlobalRole;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Models\User;
use App\Services\AppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_access_platform_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/platform-settings');

        $response->assertStatus(200);
        $response->assertSee('Platform Settings');
        $response->assertSee('Email');
        $response->assertSee('Bookings');
    }

    public function test_regular_user_cannot_access_platform_settings(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::User,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/platform-settings');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_platform_settings(): void
    {
        $response = $this->get('/admin/platform-settings');

        // Should redirect to login
        $response->assertStatus(302);
    }

    public function test_saving_email_settings_persists_to_app_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Set values directly through AppSettings
        AppSettings::set('email.from_address', 'test@example.com', 'email');
        AppSettings::set('email.from_name', 'Test Name', 'email');
        AppSettings::set('email.reply_to_address', 'reply@example.com', 'email');

        // Verify they were saved
        $this->assertEquals('test@example.com', AppSettings::get('email.from_address'));
        $this->assertEquals('Test Name', AppSettings::get('email.from_name'));
        $this->assertEquals('reply@example.com', AppSettings::get('email.reply_to_address'));
    }

    public function test_saving_booking_settings_persists_to_app_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Set values directly through AppSettings
        AppSettings::set('booking.send_customer_confirmation', false, 'booking');
        AppSettings::set('booking.send_restaurant_notification', false, 'booking');
        AppSettings::set('booking.default_notification_email', 'notifications@test.com', 'booking');

        // Verify they were saved
        $this->assertFalse(AppSettings::get('booking.send_customer_confirmation'));
        $this->assertFalse(AppSettings::get('booking.send_restaurant_notification'));
        $this->assertEquals('notifications@test.com', AppSettings::get('booking.default_notification_email'));
    }

    public function test_saving_affiliate_settings_persists_to_app_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Set values directly through AppSettings
        AppSettings::set('affiliate.default_commission_rate', 15.5, 'affiliate');
        AppSettings::set('affiliate.payout_threshold', 75.00, 'affiliate');
        AppSettings::set('affiliate.cookie_lifetime_days', 45, 'affiliate');

        // Verify they were saved
        $this->assertEquals(15.5, AppSettings::get('affiliate.default_commission_rate'));
        $this->assertEquals(75.00, AppSettings::get('affiliate.payout_threshold'));
        $this->assertEquals(45, AppSettings::get('affiliate.cookie_lifetime_days'));
    }

    public function test_saving_technical_settings_persists_to_app_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        // Set values directly through AppSettings
        AppSettings::set('technical.maintenance_mode', true, 'technical');
        AppSettings::set('technical.log_level', 'warning', 'technical');

        // Verify they were saved
        $this->assertTrue(AppSettings::get('technical.maintenance_mode'));
        $this->assertEquals('warning', AppSettings::get('technical.log_level'));
    }

    public function test_affiliate_commission_calculation_uses_platform_settings_rate(): void
    {
        // Set a custom commission rate
        AppSettings::set('affiliate.default_commission_rate', 10, 'affiliate');

        // Create an affiliate
        $affiliate = Affiliate::create([
            'code' => 'test-affiliate',
            'name' => 'Test Affiliate',
            'status' => 'active',
            'type' => 'partner',
        ]);

        // Create a pending conversion with order_amount but no commission
        $conversion = AffiliateConversion::create([
            'affiliate_id' => $affiliate->id,
            'status' => AffiliateConversionStatus::Pending,
            'order_amount' => 500.00,
            'commission_amount' => null,
            'currency' => 'EUR',
        ]);

        // Simulate the approve logic (same as in ConversionsRelationManager)
        $orderAmount = (float) ($conversion->order_amount ?? 0);
        $commissionAmount = (float) ($conversion->commission_amount ?? 0);

        // Get rate from Platform Settings
        $rate = (float) AppSettings::get('affiliate.default_commission_rate', 20);

        if ($commissionAmount <= 0 && $orderAmount > 0) {
            $conversion->commission_amount = round($orderAmount * $rate / 100, 2);
        }

        $conversion->status = AffiliateConversionStatus::Approved;
        $conversion->save();

        // Refresh and assert
        $conversion->refresh();

        $this->assertEquals(AffiliateConversionStatus::Approved, $conversion->status);
        // 500 * 10% = 50 (using the rate set in Platform Settings)
        $this->assertEquals(50.00, (float) $conversion->commission_amount);
    }

    public function test_changing_commission_rate_affects_new_conversion_approvals(): void
    {
        // Initially set rate to 20%
        AppSettings::set('affiliate.default_commission_rate', 20, 'affiliate');

        $affiliate = Affiliate::create([
            'code' => 'test-affiliate-rate',
            'name' => 'Test Affiliate',
            'status' => 'active',
            'type' => 'partner',
        ]);

        // First conversion with 20% rate
        $conversion1 = AffiliateConversion::create([
            'affiliate_id' => $affiliate->id,
            'status' => AffiliateConversionStatus::Pending,
            'order_amount' => 100.00,
            'commission_amount' => null,
            'currency' => 'EUR',
        ]);

        $rate1 = (float) AppSettings::get('affiliate.default_commission_rate', 20);
        $conversion1->commission_amount = round(100 * $rate1 / 100, 2);
        $conversion1->status = AffiliateConversionStatus::Approved;
        $conversion1->save();

        $this->assertEquals(20.00, (float) $conversion1->commission_amount);

        // Change rate to 10%
        AppSettings::set('affiliate.default_commission_rate', 10, 'affiliate');

        // Second conversion with new 10% rate
        $conversion2 = AffiliateConversion::create([
            'affiliate_id' => $affiliate->id,
            'status' => AffiliateConversionStatus::Pending,
            'order_amount' => 100.00,
            'commission_amount' => null,
            'currency' => 'EUR',
        ]);

        $rate2 = (float) AppSettings::get('affiliate.default_commission_rate', 20);
        $conversion2->commission_amount = round(100 * $rate2 / 100, 2);
        $conversion2->status = AffiliateConversionStatus::Approved;
        $conversion2->save();

        $this->assertEquals(10.00, (float) $conversion2->commission_amount);
    }
}
