<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicVenueController extends Controller
{
    /**
     * Find restaurant by route parameters with strict ISO2 country and city slug validation.
     *
     * Contract:
     * - URL pattern: /{countryIso2}/{citySlug}/{venueSlug}
     * - Country MUST be ISO2 code (lowercase) from Country.code field
     * - City MUST be slug derived from City.name via Str::slug()
     * - Venue MUST match Restaurant.slug (canonical public slug)
     *
     * Resolution logic:
     * 1. Find restaurant by slug
     * 2. Validate ISO2 country code matches (strict, lowercase)
     * 3. Canonicalize city slug and 301 redirect if mismatch
     * 4. Return 404 if country or venue don't match
     */
    protected function findRestaurantByRoute(string $country, string $city, string $venueSlug): Restaurant|RedirectResponse
    {
        // Step 1: Find restaurant by canonical public slug with eager-loaded relationships
        $restaurant = Restaurant::query()
            ->where('slug', $venueSlug)
            ->with(['city.country', 'cuisine'])
            ->first();

        if (! $restaurant) {
            abort(404, 'Venue not found');
        }

        // Ensure city relationship exists
        // Use city() method to avoid conflict with legacy 'city' text attribute
        $restaurantCity = $restaurant->city()->first();
        if (! $restaurantCity) {
            abort(404, 'Venue city not configured');
        }

        // Ensure country relationship exists
        // Use country() method to get relationship and avoid conflict with legacy 'country' text attribute
        $cityCountry = $restaurantCity->country()->first();
        if (! $cityCountry) {
            abort(404, 'Venue country not configured');
        }

        // Step 2: Validate ISO2 country code (strict lowercase matching)
        $dbCountryIso2 = strtolower($cityCountry->code);
        $urlCountryIso2 = strtolower($country);

        if ($dbCountryIso2 !== $urlCountryIso2) {
            // Country mismatch = hard 404 (do NOT redirect)
            abort(404, 'Venue not found in this country');
        }

        // Step 3: Canonical city slug handling
        $canonicalCitySlug = Str::slug($restaurantCity->name);

        if ($canonicalCitySlug !== '' && $city !== $canonicalCitySlug) {
            // City slug mismatch = 301 redirect to canonical URL
            return redirect()->to(
                route(request()->route()->getName(), [
                    'country' => $urlCountryIso2,
                    'city' => $canonicalCitySlug,
                    'venue' => $venueSlug,
                ]),
                301
            );
        }

        return $restaurant;
    }

    /**
     * Show venue public profile page.
     */
    public function show(string $country, string $city, string $venue): View|RedirectResponse
    {
        $result = $this->findRestaurantByRoute($country, $city, $venue);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $restaurant = $result;

        // Safely resolve cuisine name
        $cuisineName = null;
        if ($restaurant->cuisine) {
            $cuisineName = is_object($restaurant->cuisine)
                ? $restaurant->cuisine->getName()
                : $restaurant->cuisine;
        }

        // Safely resolve city and country using relationship methods
        $restaurantCity = $restaurant->city()->first();
        $cityCountry = $restaurantCity->country()->first();
        $cityName = $restaurantCity->name;
        $countryName = $cityCountry->name;
        $countryCode = strtoupper($country);

        // Extract additional venue info from settings
        $tagline = $restaurant->settings['tagline'] ?? null;
        $description = $restaurant->settings['description'] ?? null;
        $websiteUrl = $restaurant->settings['website_url'] ?? null;
        $phone = $restaurant->settings['phone'] ?? null;
        $email = $restaurant->settings['email'] ?? null;

        // Normalize website URL (ensure it has protocol)
        if ($websiteUrl && ! preg_match('/^https?:\/\//i', $websiteUrl)) {
            $websiteUrl = 'https://' . $websiteUrl;
        }

        // Get booking opening hours for the week
        $openingHours = $restaurant->openingHours()
            ->where('profile', 'booking')
            ->orderBy('day_of_week')
            ->get();

        // Determine today's day of week (0=Monday, 6=Sunday in OpeningHour model)
        $timezone = $restaurant->timezone ?? config('app.timezone', 'UTC');
        $now = now($timezone);
        $todayDayOfWeek = ($now->dayOfWeek + 6) % 7; // Convert PHP's 0=Sunday to 0=Monday

        return view('public.venue.show', [
            'restaurant' => $restaurant,
            'cuisineName' => $cuisineName,
            'cityName' => $cityName,
            'countryName' => $countryName,
            'countryCode' => $countryCode,
            'country' => $country,
            'city' => $city,
            'venue' => $venue,
            'tagline' => $tagline,
            'description' => $description,
            'websiteUrl' => $websiteUrl,
            'phone' => $phone,
            'email' => $email,
            'openingHours' => $openingHours,
            'todayDayOfWeek' => $todayDayOfWeek,
        ]);
    }

    /**
     * Show venue menu page (skeleton for now).
     */
    public function showMenu(string $country, string $city, string $venue): View|RedirectResponse
    {
        $result = $this->findRestaurantByRoute($country, $city, $venue);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $restaurant = $result;

        // Safely resolve cuisine name
        $cuisineName = null;
        if ($restaurant->cuisine) {
            $cuisineName = is_object($restaurant->cuisine)
                ? $restaurant->cuisine->getName()
                : $restaurant->cuisine;
        }

        // Safely resolve city and country using relationship methods
        $restaurantCity = $restaurant->city()->first();
        $cityCountry = $restaurantCity->country()->first();
        $cityName = $restaurantCity->name;
        $countryName = $cityCountry->name;
        $countryCode = strtoupper($country);

        return view('public.venue.menu', [
            'restaurant' => $restaurant,
            'cuisineName' => $cuisineName,
            'cityName' => $cityName,
            'countryName' => $countryName,
            'countryCode' => $countryCode,
            'country' => $country,
            'city' => $city,
            'venue' => $venue,
        ]);
    }
}
