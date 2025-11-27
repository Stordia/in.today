<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RestaurantPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'timezone',
        'country',
        'settings',
        'plan',
        'is_active',
        'is_verified',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'plan' => RestaurantPlan::class,
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
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

    public function restaurantUsers(): HasMany
    {
        return $this->hasMany(RestaurantUser::class);
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
}
