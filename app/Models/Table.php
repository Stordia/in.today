<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'seats',
        'min_guests',
        'max_guests',
        'zone',
        'is_combinable',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'min_guests' => 'integer',
            'max_guests' => 'integer',
            'is_combinable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
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

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
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

    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForGuests($query, int $guests)
    {
        return $query->where('min_guests', '<=', $guests)
            ->where(function ($q) use ($guests) {
                $q->whereNull('max_guests')
                    ->orWhere('max_guests', '>=', $guests);
            });
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

    public function canAccommodate(int $guests): bool
    {
        if ($guests < $this->min_guests) {
            return false;
        }

        if ($this->max_guests !== null && $guests > $this->max_guests) {
            return false;
        }

        return $guests <= $this->seats;
    }

    public function getCapacityRange(): string
    {
        if ($this->max_guests !== null) {
            return "{$this->min_guests}-{$this->max_guests}";
        }

        return "{$this->min_guests}-{$this->seats}";
    }
}
