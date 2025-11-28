<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AffiliateConversion;
use App\Models\ContactLead;
use Illuminate\Support\Facades\Log;

class ContactLeadObserver
{
    /**
     * Handle the ContactLead "created" event.
     *
     * When a new lead is created with affiliate attribution,
     * automatically create an AffiliateConversion record.
     */
    public function created(ContactLead $lead): void
    {
        // Only create conversion if this is an affiliate-attributed lead
        if (! $lead->affiliate_link_id) {
            return;
        }

        try {
            $conversion = AffiliateConversion::create([
                'affiliate_id' => $lead->affiliate_id,
                'affiliate_link_id' => $lead->affiliate_link_id,
                'restaurant_id' => null, // Not a customer yet
                'contact_lead_id' => $lead->id,
                'status' => 'pending',
                'commission_amount' => 0, // Will be set when approved
                'currency' => 'EUR',
                'occurred_at' => now(),
            ]);

            // Increment conversions_count on the affiliate link
            $lead->affiliateLink?->increment('conversions_count');

            Log::info('Affiliate conversion created for new lead', [
                'contact_lead_id' => $lead->id,
                'affiliate_conversion_id' => $conversion->id,
                'affiliate_id' => $lead->affiliate_id,
                'affiliate_link_id' => $lead->affiliate_link_id,
            ]);
        } catch (\Throwable $e) {
            // Non-fatal: log the error but don't break the lead creation
            Log::error('Failed to create affiliate conversion for lead', [
                'contact_lead_id' => $lead->id,
                'affiliate_link_id' => $lead->affiliate_link_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
