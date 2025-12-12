<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicCityController extends Controller
{
    /**
     * Show the home page with city search.
     */
    public function home(): View
    {
        // Get all cities that have at least one restaurant with booking enabled
        $cities = City::query()
            ->whereHas('restaurants', function ($query) {
                $query->where('booking_enabled', true);
            })
            ->with('country')
            ->orderBy('name')
            ->get();

        // Count total venues across all cities
        $totalVenues = Restaurant::query()
            ->where('booking_enabled', true)
            ->count();

        return view('public.home', [
            'cities' => $cities,
            'totalVenues' => $totalVenues,
        ]);
    }

    /**
     * Handle city search form submission.
     */
    public function search(): RedirectResponse
    {
        $cityId = request()->input('city_id');

        $city = City::find($cityId);

        if (! $city) {
            return redirect()->route('root')->with('error', 'Please select a valid city.');
        }

        // Use relationship method to avoid conflict with legacy text field
        $cityCountry = $city->country()->first();
        if (! $cityCountry) {
            return redirect()->route('root')->with('error', 'City configuration error.');
        }

        $countryIso2 = strtolower($cityCountry->code);
        $citySlug = Str::slug($city->name);

        return redirect()->route('public.city.show', [
            'country' => $countryIso2,
            'city' => $citySlug,
        ]);
    }

    /**
     * Show city results page with all venues in the city.
     *
     * Reuses the same slug canonicalization logic as venue pages:
     * - Country must be ISO2 code (lowercase)
     * - City slug must match Str::slug(city.name)
     * - 301 redirect if slug mismatch
     * - 404 if not found
     */
    public function show(string $country, string $city): View|RedirectResponse
    {
        // Find city by slug with country relationship
        $cityModel = City::query()
            ->where('slug', $city)
            ->whereHas('country', function ($query) use ($country) {
                $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
            })
            ->with(['country', 'restaurants' => function ($query) {
                $query->where('booking_enabled', true)
                    ->with(['cuisine'])
                    ->orderBy('name');
            }])
            ->first();

        // If city not found by slug, try to find it and redirect to canonical URL
        if (! $cityModel) {
            // Try to find any city with this slug regardless of country
            $possibleCity = City::where('slug', $city)
                ->with('country')
                ->first();

            if ($possibleCity) {
                // Wrong country in URL, return 404
                abort(404, 'City not found in this country');
            }

            // City doesn't exist at all
            abort(404, 'City not found');
        }

        // Use relationship method to avoid conflict with legacy text field
        $cityCountry = $cityModel->country()->first();
        if (! $cityCountry) {
            abort(404, 'City country not configured');
        }

        // Validate country code matches
        $dbCountryIso2 = strtolower($cityCountry->code);
        $urlCountryIso2 = strtolower($country);

        if ($dbCountryIso2 !== $urlCountryIso2) {
            abort(404, 'City not found in this country');
        }

        // Canonical city slug handling
        $canonicalCitySlug = Str::slug($cityModel->name);

        if ($canonicalCitySlug !== '' && $city !== $canonicalCitySlug) {
            // City slug mismatch = 301 redirect to canonical URL
            return redirect()->route('public.city.show', [
                'country' => $urlCountryIso2,
                'city' => $canonicalCitySlug,
            ], 301);
        }

        // Get restaurants with booking enabled for this city
        $restaurants = $cityModel->restaurants;

        return view('public.city.show', [
            'city' => $cityModel,
            'country' => $cityCountry,
            'restaurants' => $restaurants,
            'countrySlug' => $urlCountryIso2,
            'citySlug' => $city,
        ]);
    }
}
