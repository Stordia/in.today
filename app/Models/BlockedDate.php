<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'profile',
        'date',
        'is_all_day',
        'time_from',
        'time_to',
        'reason',
    ];

    protected $attributes = [
        'profile' => 'booking',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_all_day' => 'boolean',
            'time_from' => 'datetime:H:i',
            'time_to' => 'datetime:H:i',
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeAllDay($query)
    {
        return $query->where('is_all_day', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeBookingProfile($query)
    {
        return $query->where('profile', 'booking');
    }

    public function scopeForProfile($query, string $profile)
    {
        return $query->where('profile', $profile);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAllDay(): bool
    {
        return $this->is_all_day;
    }

    /**
     * Check if a given time falls within this blocked period.
     */
    public function blocksTime(string $time): bool
    {
        if ($this->is_all_day) {
            return true;
        }

        if ($this->time_from === null || $this->time_to === null) {
            return true;
        }

        return $time >= $this->time_from && $time <= $this->time_to;
    }
}
