<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RestaurantRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => RestaurantRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeByRole($query, RestaurantRole $role)
    {
        return $query->where('role', $role);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isOwner(): bool
    {
        return $this->role === RestaurantRole::Owner;
    }

    public function isManager(): bool
    {
        return $this->role === RestaurantRole::Manager;
    }

    public function isStaff(): bool
    {
        return $this->role === RestaurantRole::Staff;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
