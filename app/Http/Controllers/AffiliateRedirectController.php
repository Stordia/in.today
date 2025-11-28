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
        $affiliateLink = AffiliateLink::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $affiliateLink) {
            Log::warning('Affiliate link not found or inactive', ['slug' => $slug]);

            return redirect()->route('landing', ['locale' => 'en']);
        }

        // Check if the affiliate is active
        if (! $affiliateLink->affiliate || ! $affiliateLink->affiliate->isActive()) {
            Log::warning('Affiliate is not active', [
                'slug' => $slug,
                'affiliate_id' => $affiliateLink->affiliate_id,
            ]);

            return redirect()->route('landing', ['locale' => 'en']);
        }

        // Increment click count
        $affiliateLink->increment('clicks_count');

        // Store affiliate info in session for later attribution
        // Using session()->put() and save() to ensure persistence across redirect
        session()->put('affiliate_link_id', $affiliateLink->id);
        session()->put('affiliate_id', $affiliateLink->affiliate_id);
        session()->save();

        Log::info('Affiliate click tracked and session saved', [
            'affiliate_link_id' => $affiliateLink->id,
            'affiliate_id' => $affiliateLink->affiliate_id,
            'slug' => $slug,
            'session_id' => session()->getId(),
        ]);

        // Determine target locale (default to 'en' for now)
        // Could be extended to parse from slug metadata or detect browser locale
        $locale = 'en';

        // Redirect to clean landing page (no query parameters)
        return redirect()->route('landing', ['locale' => $locale]);
    }
}
