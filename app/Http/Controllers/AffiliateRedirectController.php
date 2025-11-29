<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AffiliateRedirectController extends Controller
{
    /**
     * Handle affiliate link redirect.
     *
     * Looks up the affiliate link by slug, increments click count,
     * stores affiliate info in session, and redirects to the landing page.
     * The final URL is always clean (no query parameters).
     */
    public function redirect(string $slug): RedirectResponse
    {
        Log::info('[AFFILIATE_DEBUG] Redirect handler invoked', [
            'slug' => $slug,
            'session_id' => session()->getId(),
            'session_driver' => config('session.driver'),
        ]);

        $affiliateLink = AffiliateLink::query()
            ->with('affiliate')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $affiliateLink) {
            Log::warning('[AFFILIATE_DEBUG] Affiliate link not found or inactive', ['slug' => $slug]);

            return redirect()->route('landing', ['locale' => 'en']);
        }

        // Check if the affiliate is active
        if (! $affiliateLink->affiliate || ! $affiliateLink->affiliate->isActive()) {
            Log::warning('[AFFILIATE_DEBUG] Affiliate is not active', [
                'slug' => $slug,
                'affiliate_id' => $affiliateLink->affiliate_id,
            ]);

            return redirect()->route('landing', ['locale' => 'en']);
        }

        // Increment click count
        $affiliateLink->increment('clicks_count');

        // Store affiliate info in session as a single consolidated array
        // This approach is more reliable than separate keys
        $affiliateData = [
            'id' => $affiliateLink->affiliate_id,
            'link_id' => $affiliateLink->id,
            'slug' => $affiliateLink->slug,
            'name' => $affiliateLink->affiliate->name,
            'timestamp' => now()->toIso8601String(),
        ];

        session()->put('affiliate', $affiliateData);
        session()->save();

        Log::info('[AFFILIATE_DEBUG] Affiliate data stored in session', [
            'affiliate_data' => $affiliateData,
            'session_id' => session()->getId(),
            'session_all_keys' => array_keys(session()->all()),
            'verification_read' => session('affiliate'),
        ]);

        // Determine target locale (default to 'en' for now)
        // Could be extended to parse from slug metadata or detect browser locale
        $locale = 'en';

        // Redirect to clean landing page (no query parameters)
        return redirect()->route('landing', ['locale' => $locale]);
    }
}
