<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RestaurantPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'agency_id',
        'city_id',
        'country_id',
        'timezone',
        'country', // legacy text field
        // Address
        'address_street',
        'address_district',
        'address_postal',
        'address_country',
        // Geo
        'latitude',
        'longitude',
        // Classification
        'cuisine_id',
        'price_range',
        // Stats
        'avg_rating',
        'review_count',
        'reservation_count',
        // Features & media
        'features',
        'logo_url',
        'cover_image_url',
        // Config
        'settings',
        'plan',
        'is_active',
        'is_verified',
        'is_featured',
        // Booking settings
        'booking_enabled',
        'booking_public_slug',
        'booking_min_party_size',
        'booking_max_party_size',
        'booking_default_duration_minutes',
        'booking_min_lead_time_minutes',
        'booking_max_lead_time_days',
        'booking_notes_internal',
        // Deposit settings
        'booking_deposit_enabled',
        'booking_deposit_threshold_party_size',
        'booking_deposit_type',
        'booking_deposit_amount',
        'booking_deposit_currency',
        'booking_deposit_policy',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'price_range' => 'integer',
            'avg_rating' => 'decimal:1',
            'review_count' => 'integer',
            'reservation_count' => 'integer',
            'features' => 'array',
            'settings' => 'array',
            'plan' => RestaurantPlan::class,
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            // Booking settings
            'booking_enabled' => 'boolean',
            'booking_min_party_size' => 'integer',
            'booking_max_party_size' => 'integer',
            'booking_default_duration_minutes' => 'integer',
            'booking_min_lead_time_minutes' => 'integer',
            'booking_max_lead_time_days' => 'integer',
            // Deposit settings
            'booking_deposit_enabled' => 'boolean',
            'booking_deposit_threshold_party_size' => 'integer',
            'booking_deposit_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Restaurant $restaurant) {
            if (empty($restaurant->uuid)) {
                $restaurant->uuid = (string) Str::uuid();
            }
            if (empty($restaurant->slug)) {
                $restaurant->slug = Str::slug($restaurant->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cuisine(): BelongsTo
    {
        return $this->belongsTo(Cuisine::class);
    }

    public function restaurantUsers(): HasMany
    {
        return $this->hasMany(RestaurantUser::class);
    }

    /**
     * Get all users linked to this restaurant via RestaurantUser pivot.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get the primary owner of this restaurant.
     * Returns the first user with 'owner' role, or null if none exists.
     */
    public function owner(): ?User
    {
        return $this->users()
            ->wherePivot('role', 'owner')
            ->wherePivot('is_active', true)
            ->first();
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByPlan($query, RestaurantPlan $plan)
    {
        return $query->where('plan', $plan);
    }

    public function scopeByAgency($query, ?int $agencyId)
    {
        return $query->where('agency_id', $agencyId);
    }

    public function scopeDirect($query)
    {
        return $query->whereNull('agency_id');
    }

    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeByCuisine($query, int $cuisineId)
    {
        return $query->where('cuisine_id', $cuisineId);
    }

    public function scopeByPriceRange($query, int $priceRange)
    {
        return $query->where('price_range', $priceRange);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function belongsToAgency(): bool
    {
        return $this->agency_id !== null;
    }

    public function isDirectCustomer(): bool
    {
        return $this->agency_id === null;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_district,
            $this->address_postal,
            $this->address_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get price range as euro symbols (€, €€, €€€, €€€€).
     */
    public function getPriceRangeSymbol(): string
    {
        if ($this->price_range === null) {
            return '';
        }

        return str_repeat('€', $this->price_range);
    }

    /*
    |--------------------------------------------------------------------------
    | Media Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the logo URL (supports both external URLs and uploaded files).
     */
    public function getLogoUrl(): ?string
    {
        if (empty($this->logo_url)) {
            return null;
        }

        // If it's already a full URL, return as-is (backwards compatibility)
        if (str_starts_with($this->logo_url, 'http://') || str_starts_with($this->logo_url, 'https://')) {
            return $this->logo_url;
        }

        // Otherwise, it's a storage path
        return asset('storage/' . $this->logo_url);
    }

    /**
     * Get the cover image URL (supports both external URLs and uploaded files).
     */
    public function getCoverImageUrl(): ?string
    {
        if (empty($this->cover_image_url)) {
            return null;
        }

        // If it's already a full URL, return as-is (backwards compatibility)
        if (str_starts_with($this->cover_image_url, 'http://') || str_starts_with($this->cover_image_url, 'https://')) {
            return $this->cover_image_url;
        }

        // Otherwise, it's a storage path
        return asset('storage/' . $this->cover_image_url);
    }

    /*
    |--------------------------------------------------------------------------
    | Booking Helpers
    |--------------------------------------------------------------------------
    */

    public function isBookingEnabled(): bool
    {
        return $this->booking_enabled;
    }

    public function getBookingUrl(): ?string
    {
        if (! $this->booking_public_slug) {
            return null;
        }

        return url("/book/{$this->booking_public_slug}");
    }

    /*
    |--------------------------------------------------------------------------
    | Deposit Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if deposit is required for a given party size.
     */
    public function requiresDeposit(int $partySize): bool
    {
        return $this->booking_deposit_enabled
            && $partySize >= ($this->booking_deposit_threshold_party_size ?? 4);
    }

    /**
     * Calculate deposit amount for a given party size.
     */
    public function calculateDepositAmount(int $partySize): float
    {
        if (! $this->requiresDeposit($partySize)) {
            return 0.00;
        }

        $amount = (float) ($this->booking_deposit_amount ?? 0.00);

        return match ($this->booking_deposit_type ?? 'fixed_per_person') {
            'fixed_per_person' => $amount * $partySize,
            'fixed_per_reservation' => $amount,
            default => $amount,
        };
    }

    /**
     * Get formatted deposit amount with currency.
     */
    public function getFormattedDepositAmount(int $partySize): string
    {
        $amount = $this->calculateDepositAmount($partySize);
        $currency = $this->booking_deposit_currency ?? 'EUR';

        return number_format($amount, 2, ',', '.') . ' ' . $currency;
    }
}
