<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GlobalRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'global_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'global_role' => GlobalRole::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filament
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Only platform admins can access the admin panel
        if ($panel->getId() === 'admin') {
            return $this->isPlatformAdmin();
        }

        // Business panel: user must have at least one active restaurant link
        if ($panel->getId() === 'business') {
            return $this->hasAnyRestaurantAccess();
        }

        return false;
    }

    /**
     * Check if the user has access to any restaurant.
     */
    public function hasAnyRestaurantAccess(): bool
    {
        return $this->restaurantUsers()
            ->where('is_active', true)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function agencyUsers(): HasMany
    {
        return $this->hasMany(AgencyUser::class);
    }

    public function restaurantUsers(): HasMany
    {
        return $this->hasMany(RestaurantUser::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePlatformAdmins($query)
    {
        return $query->where('global_role', GlobalRole::PlatformAdmin);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPlatformAdmin(): bool
    {
        return $this->global_role === GlobalRole::PlatformAdmin;
    }
}
