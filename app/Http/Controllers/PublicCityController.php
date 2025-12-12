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
        $citySlug = $city->slug_canonical ?: Str::slug($city->name);

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
     * - City resolved by slug_canonical column (indexed, fast lookup)
     * - Supports legacy slugs (katerini-gr) for backward compatibility
     * - 301 redirect to canonical if incoming slug != slug_canonical
     * - Avoids runtime Str::slug(name) and loading all cities into memory
     */
    public function show(string $country, string $city): View|RedirectResponse
    {
        // Step 1: Try to find city by canonical slug (fast indexed lookup)
        $cityModel = City::query()
            ->whereHas('country', function ($query) use ($country) {
                $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
            })
            ->where('slug_canonical', $city)
            ->with(['country', 'restaurants' => function ($query) {
                $query->where('booking_enabled', true)
                    ->with(['cuisine'])
                    ->orderBy('name');
            }])
            ->first();

        // Step 2: If not found by canonical, try legacy slug field
        if (! $cityModel) {
            $cityModel = City::query()
                ->whereHas('country', function ($query) use ($country) {
                    $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
                })
                ->where('slug', $city)
                ->with(['country', 'restaurants' => function ($query) {
                    $query->where('booking_enabled', true)
                        ->with(['cuisine'])
                        ->orderBy('name');
                }])
                ->first();

            // If found by legacy slug, redirect to canonical
            if ($cityModel && $cityModel->slug_canonical && $city !== $cityModel->slug_canonical) {
                $cityCountry = $cityModel->country()->first();
                $urlCountryIso2 = strtolower($country);

                return redirect()->route('public.city.show', [
                    'country' => $urlCountryIso2,
                    'city' => $cityModel->slug_canonical,
                ], 301);
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

        // Step 5: Return city results view
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
