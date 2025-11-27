<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Agency;
use App\Models\Restaurant;

/**
 * Lightweight tenancy helper for managing the current agency/restaurant context.
 *
 * This is a simple request-scoped container for tracking which tenant context
 * is active. It does NOT automatically scope queries or enforce authorization.
 *
 * Usage:
 *   $tenant = app(CurrentTenant::class);
 *   $tenant->setRestaurant($restaurant);
 *   $restaurant = $tenant->restaurant();
 *
 * In future phases, this will be wired into middleware to automatically resolve
 * the tenant from the authenticated user or request context.
 */
class CurrentTenant
{
    private ?Agency $agency = null;

    private ?Restaurant $restaurant = null;

    public function setAgency(?Agency $agency): self
    {
        $this->agency = $agency;

        return $this;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        // If restaurant belongs to an agency, set that too
        if ($restaurant !== null && $restaurant->agency_id !== null) {
            $this->agency = $restaurant->agency;
        }

        return $this;
    }

    public function agency(): ?Agency
    {
        return $this->agency;
    }

    public function restaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function hasAgency(): bool
    {
        return $this->agency !== null;
    }

    public function hasRestaurant(): bool
    {
        return $this->restaurant !== null;
    }

    public function agencyId(): ?int
    {
        return $this->agency?->id;
    }

    public function restaurantId(): ?int
    {
        return $this->restaurant?->id;
    }

    public function clear(): self
    {
        $this->agency = null;
        $this->restaurant = null;

        return $this;
    }

    /**
     * Check if we are operating in agency context (agency panel).
     */
    public function isAgencyContext(): bool
    {
        return $this->hasAgency() && ! $this->hasRestaurant();
    }

    /**
     * Check if we are operating in restaurant context (restaurant panel).
     */
    public function isRestaurantContext(): bool
    {
        return $this->hasRestaurant();
    }
}
