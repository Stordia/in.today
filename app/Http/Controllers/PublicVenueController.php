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
     * Find restaurant by route parameters with canonical URL validation.
     *
     * Resolution logic:
     * 1. Find restaurant by booking_public_slug (must have booking_enabled=true)
     * 2. Validate country code matches
     * 3. Canonicalize city slug and redirect if mismatch
     */
    protected function findRestaurantByRoute(string $country, string $city, string $venueSlug): Restaurant|RedirectResponse
    {
        // Find restaurant by booking_public_slug with relations
        $restaurant = Restaurant::query()
            ->where('booking_enabled', true)
            ->where('booking_public_slug', $venueSlug)
            ->with('city.country', 'cuisine')
            ->first();

        if (! $restaurant) {
            abort(404, 'Venue not found');
        }

        // Validate country code
        $dbCountryCode = strtolower($restaurant->city?->country?->code ?? '');

        if ($dbCountryCode !== strtolower($country)) {
            abort(404, 'Venue not found in this country');
        }

        // Canonical city handling
        $expectedCitySlug = Str::slug($restaurant->city?->name ?? '');

        if ($expectedCitySlug !== '' && $city !== $expectedCitySlug) {
            // Redirect to canonical URL
            return redirect()->to(
                route(request()->route()->getName(), [
                    'country' => $country,
                    'city' => $expectedCitySlug,
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

        // Safely resolve city and country
        $cityName = $restaurant->city?->name ?? null;
        $countryName = $restaurant->city?->country?->name ?? null;
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

        // Safely resolve city and country
        $cityName = $restaurant->city?->name ?? null;
        $countryName = $restaurant->city?->country?->name ?? null;
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
