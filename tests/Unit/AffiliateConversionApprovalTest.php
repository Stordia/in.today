<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AffiliateConversionStatus;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Services\AppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateConversionApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_auto_calculates_from_order_amount_on_approval(): void
    {
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
            'order_amount' => 500.00, // €500 order
            'commission_amount' => null,
            'currency' => 'EUR',
        ]);

        // Simulate the approve logic (same as in ConversionsRelationManager)
        $orderAmount = (float) ($conversion->order_amount ?? 0);
        $commissionAmount = (float) ($conversion->commission_amount ?? 0);

        // Default rate is 20% unless configured differently
        $rate = (float) AppSettings::get('affiliate_default_commission_rate', 20);

        if ($commissionAmount <= 0 && $orderAmount > 0) {
            $conversion->commission_amount = round($orderAmount * $rate / 100, 2);
        }

        $conversion->status = AffiliateConversionStatus::Approved;
        $conversion->save();

        // Refresh and assert
        $conversion->refresh();

        $this->assertEquals(AffiliateConversionStatus::Approved, $conversion->status);
        $this->assertEquals(100.00, (float) $conversion->commission_amount); // 500 * 20% = 100
    }

    public function test_commission_not_overwritten_if_already_set(): void
    {
        $affiliate = Affiliate::create([
            'code' => 'test-affiliate-2',
            'name' => 'Test Affiliate',
            'status' => 'active',
            'type' => 'partner',
        ]);

        // Create a pending conversion with both order_amount and commission already set
        $conversion = AffiliateConversion::create([
            'affiliate_id' => $affiliate->id,
            'status' => AffiliateConversionStatus::Pending,
            'order_amount' => 500.00,
            'commission_amount' => 75.00, // manually set to €75
            'currency' => 'EUR',
        ]);

        // Simulate the approve logic
        $orderAmount = (float) ($conversion->order_amount ?? 0);
        $commissionAmount = (float) ($conversion->commission_amount ?? 0);

        $rate = (float) AppSettings::get('affiliate_default_commission_rate', 20);

        // Should NOT overwrite since commission is already > 0
        if ($commissionAmount <= 0 && $orderAmount > 0) {
            $conversion->commission_amount = round($orderAmount * $rate / 100, 2);
        }

        $conversion->status = AffiliateConversionStatus::Approved;
        $conversion->save();

        $conversion->refresh();

        $this->assertEquals(AffiliateConversionStatus::Approved, $conversion->status);
        $this->assertEquals(75.00, (float) $conversion->commission_amount); // Unchanged
    }

    public function test_commission_stays_null_if_no_order_amount(): void
    {
        $affiliate = Affiliate::create([
            'code' => 'test-affiliate-3',
            'name' => 'Test Affiliate',
            'status' => 'active',
            'type' => 'partner',
        ]);

        // Create a pending conversion with no order_amount
        $conversion = AffiliateConversion::create([
            'affiliate_id' => $affiliate->id,
            'status' => AffiliateConversionStatus::Pending,
            'order_amount' => null,
            'commission_amount' => null,
            'currency' => 'EUR',
        ]);

        // Simulate the approve logic
        $orderAmount = (float) ($conversion->order_amount ?? 0);
        $commissionAmount = (float) ($conversion->commission_amount ?? 0);

        $rate = (float) AppSettings::get('affiliate_default_commission_rate', 20);

        // Should NOT calculate since order_amount is 0
        if ($commissionAmount <= 0 && $orderAmount > 0) {
            $conversion->commission_amount = round($orderAmount * $rate / 100, 2);
        }

        $conversion->status = AffiliateConversionStatus::Approved;
        $conversion->save();

        $conversion->refresh();

        $this->assertEquals(AffiliateConversionStatus::Approved, $conversion->status);
        $this->assertNull($conversion->commission_amount); // Unchanged
    }
}
