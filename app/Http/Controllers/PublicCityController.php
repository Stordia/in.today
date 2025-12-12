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
     * City resolution strategy:
     * - Country must be ISO2 code (lowercase)
     * - City resolved by canonical slug derived from city.name (NOT cities.slug field)
     * - Supports both canonical (katerini) and legacy slugs (katerini-gr)
     * - 301 redirect to canonical if incoming slug != canonical
     * - DB cities.slug can remain legacy (e.g., katerini-gr) without breaking routes
     */
    public function show(string $country, string $city): View|RedirectResponse
    {
        // Step 1: Find all cities for the given country
        $cities = City::query()
            ->whereHas('country', function ($query) use ($country) {
                $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
            })
            ->with(['country', 'restaurants' => function ($query) {
                $query->where('booking_enabled', true)
                    ->with(['cuisine'])
                    ->orderBy('name');
            }])
            ->get();

        // Step 2: Find city by matching canonical slug (derived from name)
        $cityModel = null;
        foreach ($cities as $possibleCity) {
            $canonicalSlug = Str::slug($possibleCity->name);
            // Match both canonical and legacy (with country suffix) slugs
            if ($city === $canonicalSlug || $city === $possibleCity->slug) {
                $cityModel = $possibleCity;
                break;
            }
        }

        if (! $cityModel) {
            abort(404, 'City not found');
        }

        // Step 3: Use relationship method to avoid conflict with legacy text field
        $cityCountry = $cityModel->country()->first();
        if (! $cityCountry) {
            abort(404, 'City country not configured');
        }

        // Step 4: Validate country code matches (redundant but safe)
        $dbCountryIso2 = strtolower($cityCountry->code);
        $urlCountryIso2 = strtolower($country);

        if ($dbCountryIso2 !== $urlCountryIso2) {
            abort(404, 'City not found in this country');
        }

        // Step 5: Canonical city slug handling - always redirect to canonical
        $canonicalCitySlug = Str::slug($cityModel->name);

        if ($canonicalCitySlug !== '' && $city !== $canonicalCitySlug) {
            // City slug mismatch = 301 redirect to canonical URL
            return redirect()->route('public.city.show', [
                'country' => $urlCountryIso2,
                'city' => $canonicalCitySlug,
            ], 301);
        }

        // Step 6: Return city results view
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
