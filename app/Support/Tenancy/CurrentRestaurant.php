<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CurrentRestaurant
{
    private const SESSION_KEY = 'current_restaurant_id';

    /**
     * Get the current restaurant for the authenticated user.
     */
    public static function get(): ?Restaurant
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $restaurants = static::getUserRestaurants($user);

        if ($restaurants->isEmpty()) {
            return null;
        }

        // If user has only one restaurant, return it
        if ($restaurants->count() === 1) {
            return $restaurants->first();
        }

        // Check session for selected restaurant
        $sessionRestaurantId = session(self::SESSION_KEY);

        if ($sessionRestaurantId) {
            $restaurant = $restaurants->firstWhere('id', $sessionRestaurantId);
            if ($restaurant) {
                return $restaurant;
            }
        }

        // Fallback: first restaurant by name
        return $restaurants->sortBy('name')->first();
    }

    /**
     * Get the current restaurant ID.
     */
    public static function id(): ?int
    {
        return static::get()?->id;
    }

    /**
     * Set the current restaurant by ID.
     */
    public static function set(int $restaurantId): void
    {
        session([self::SESSION_KEY => $restaurantId]);
    }

    /**
     * Clear the current restaurant from session.
     */
    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Get all restaurants the user has access to.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Restaurant>
     */
    public static function getUserRestaurants(?User $user = null): \Illuminate\Database\Eloquent\Collection
    {
        $user = $user ?? Auth::user();

        if (! $user instanceof User) {
            return collect();
        }

        return Restaurant::query()
            ->whereHas('restaurantUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('is_active', true);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if the user has access to the given restaurant.
     */
    public static function userHasAccess(int $restaurantId, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->restaurantUsers()
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if the user has any restaurant access.
     */
    public static function userHasAnyRestaurant(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->restaurantUsers()
            ->where('is_active', true)
            ->exists();
    }
}
