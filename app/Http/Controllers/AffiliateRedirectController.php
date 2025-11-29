<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AffiliateRedirectController extends Controller
{
    /**
     * Cookie name for affiliate attribution.
     */
    public const COOKIE_NAME = 'intoday_affiliate';

    /**
     * Cookie lifetime in minutes (30 days).
     */
    public const COOKIE_LIFETIME_MINUTES = 60 * 24 * 30;

    /**
     * Handle affiliate link redirect.
     *
     * Looks up the affiliate link by slug, increments click count,
     * stores affiliate info in session AND cookie, and redirects to the landing page.
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

        // Build affiliate data for both session and cookie
        // Using consistent keys for both storage methods
        $affiliateData = [
            'affiliate_id' => $affiliateLink->affiliate_id,
            'affiliate_link_id' => $affiliateLink->id,
            'slug' => $affiliateLink->slug,
            'name' => $affiliateLink->affiliate->name,
            'ts' => now()->toIso8601String(),
        ];

        // Store in session (immediate attribution for same-session form submissions)
        session()->put('affiliate', $affiliateData);
        session()->save();

        Log::info('[AFFILIATE_DEBUG] Affiliate data stored in session', [
            'affiliate_data' => $affiliateData,
            'session_id' => session()->getId(),
            'session_all_keys' => array_keys(session()->all()),
            'verification_read' => session('affiliate'),
        ]);

        // Create cookie for 30-day attribution window (last-click wins)
        $cookie = Cookie::make(
            self::COOKIE_NAME,
            json_encode($affiliateData),
            self::COOKIE_LIFETIME_MINUTES,
            '/',
            null,
            null,
            true // HttpOnly
        );

        Log::info('[AFFILIATE_DEBUG_COOKIE] Cookie set for affiliate attribution', [
            'cookie_name' => self::COOKIE_NAME,
            'affiliate_id' => $affiliateData['affiliate_id'],
            'affiliate_link_id' => $affiliateData['affiliate_link_id'],
            'slug' => $affiliateData['slug'],
            'expires_in_days' => 30,
            'session_id' => session()->getId(),
        ]);

        // Determine target locale (default to 'en' for now)
        $locale = 'en';

        // Redirect to clean landing page with cookie attached
        return redirect()->route('landing', ['locale' => $locale])->withCookie($cookie);
    }
}
