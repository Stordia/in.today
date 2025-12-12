<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Cuisine;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * - Applies filters from session (no URL query params)
     */
    public function show(string $country, string $city): View|RedirectResponse
    {
        $urlCountryIso2 = strtolower($country);

        // Step 1: Try to find city by canonical slug (fast indexed lookup)
        $cityModel = City::query()
            ->whereHas('country', function ($query) use ($urlCountryIso2) {
                $query->whereRaw('LOWER(code) = ?', [$urlCountryIso2]);
            })
            ->where('slug_canonical', $city)
            ->with('country')
            ->first();

        // Step 2: If not found by canonical, try legacy slug field
        if (! $cityModel) {
            $cityModel = City::query()
                ->whereHas('country', function ($query) use ($urlCountryIso2) {
                    $query->whereRaw('LOWER(code) = ?', [$urlCountryIso2]);
                })
                ->where('slug', $city)
                ->with('country')
                ->first();

            // If found by legacy slug, redirect to canonical
            if ($cityModel && $cityModel->slug_canonical && $city !== $cityModel->slug_canonical) {
                return redirect()->route('public.city.show', [
                    'country' => $urlCountryIso2,
                    'city' => $cityModel->slug_canonical,
                ], 301);
            }
        }

        // Step 3: If still not found, try legacy pattern: {citySlug}-{countryIso2}
        // e.g. athens-gr, berlin-de
        if (! $cityModel) {
            $legacySlug = $city . '-' . $urlCountryIso2;
            $cityModel = City::query()
                ->whereHas('country', function ($query) use ($urlCountryIso2) {
                    $query->whereRaw('LOWER(code) = ?', [$urlCountryIso2]);
                })
                ->where(function ($query) use ($legacySlug) {
                    $query->where('slug', $legacySlug)
                        ->orWhere('slug_canonical', $legacySlug);
                })
                ->with('country')
                ->first();

            // If found by legacy pattern, redirect to canonical
            if ($cityModel && $cityModel->slug_canonical && $city !== $cityModel->slug_canonical) {
                return redirect()->route('public.city.show', [
                    'country' => $urlCountryIso2,
                    'city' => $cityModel->slug_canonical,
                ], 301);
            }
        }

        if (! $cityModel) {
            abort(404, 'City not found');
        }

        // Step 4: Use relationship method to avoid conflict with legacy text field
        $cityCountry = $cityModel->country()->first();
        if (! $cityCountry) {
            abort(404, 'City country not configured');
        }

        // Step 5: Validate country code matches (redundant but safe)
        $dbCountryIso2 = strtolower($cityCountry->code);

        if ($dbCountryIso2 !== $urlCountryIso2) {
            abort(404, 'City not found in this country');
        }

        // Step 6: Get filters from session
        $filterKey = "public-city-filters:{$urlCountryIso2}:{$cityModel->id}";
        $filters = session($filterKey, [
            'cuisine_id' => null,
            'booking_only' => false, // Default OFF: show ALL venues (booking is optional)
            'open_today' => false,   // Default OFF - most users want to see all venues; they can filter if needed
        ]);

        // Step 7: Build restaurant query with filters
        $restaurantsQuery = Restaurant::query()
            ->where('city_id', $cityModel->id)
            ->with(['cuisine']);

        // Apply booking filter
        if ($filters['booking_only']) {
            $restaurantsQuery->where('booking_enabled', true);
        }

        // Apply cuisine filter
        if (! empty($filters['cuisine_id'])) {
            $restaurantsQuery->where('cuisine_id', $filters['cuisine_id']);
        }

        // Apply "open today" filter
        if ($filters['open_today']) {
            $timezone = $cityModel->timezone ?? config('app.timezone', 'UTC');
            $now = Carbon::now($timezone);
            $todayDayOfWeek = ($now->dayOfWeek + 6) % 7; // Convert PHP's 0=Sunday to 0=Monday

            // Get restaurant IDs that are open today
            $openTodayIds = \DB::table('opening_hours')
                ->where('profile', 'booking')
                ->where('day_of_week', $todayDayOfWeek)
                ->where('is_open', true)
                ->pluck('restaurant_id')
                ->unique()
                ->toArray();

            if (! empty($openTodayIds)) {
                $restaurantsQuery->whereIn('id', $openTodayIds);
            } else {
                // No venues open today
                $restaurantsQuery->whereRaw('1 = 0'); // Empty result
            }
        }

        $restaurants = $restaurantsQuery->orderBy('name')->get();

        // Step 8: Get all cuisines for filter dropdown
        $cuisines = Cuisine::query()
            ->whereHas('restaurants', function ($query) use ($cityModel) {
                $query->where('city_id', $cityModel->id);
            })
            ->ordered()
            ->get();

        return view('public.city.show', [
            'city' => $cityModel,
            'country' => $cityCountry,
            'restaurants' => $restaurants,
            'countrySlug' => $urlCountryIso2,
            'citySlug' => $city,
            'cuisines' => $cuisines,
            'filters' => $filters,
        ]);
    }

    /**
     * Apply filters and store in session.
     * No URL query params - clean redirect back to city page.
     */
    public function applyFilters(Request $request, string $country, string $city): RedirectResponse
    {
        // Resolve city to get ID for session key
        $cityModel = City::query()
            ->whereHas('country', function ($query) use ($country) {
                $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
            })
            ->where('slug_canonical', $city)
            ->first();

        if (! $cityModel) {
            return redirect()->route('public.city.show', compact('country', 'city'));
        }

        $filterKey = "public-city-filters:{$country}:{$cityModel->id}";

        session()->put($filterKey, [
            'cuisine_id' => $request->input('cuisine_id') ?: null,
            'booking_only' => $request->boolean('booking_only'),
            'open_today' => $request->boolean('open_today'),
        ]);

        // Clean redirect back to city page (no query params)
        return redirect()->route('public.city.show', compact('country', 'city'));
    }

    /**
     * Clear all filters for this city.
     */
    public function clearFilters(string $country, string $city): RedirectResponse
    {
        // Resolve city to get ID for session key
        $cityModel = City::query()
            ->whereHas('country', function ($query) use ($country) {
                $query->whereRaw('LOWER(code) = ?', [strtolower($country)]);
            })
            ->where('slug_canonical', $city)
            ->first();

        if ($cityModel) {
            $filterKey = "public-city-filters:{$country}:{$cityModel->id}";
            session()->forget($filterKey);
        }

        // Clean redirect back to city page
        return redirect()->route('public.city.show', compact('country', 'city'));
    }
}
