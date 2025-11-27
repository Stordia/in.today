<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WaitlistStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use HasFactory;

    protected $table = 'waitlist';

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'date',
        'preferred_time',
        'guests',
        'status',
        'notified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'preferred_time' => 'datetime:H:i',
            'guests' => 'integer',
            'status' => WaitlistStatus::class,
            'notified_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByStatus($query, WaitlistStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            WaitlistStatus::Waiting,
            WaitlistStatus::Notified,
        ]);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', WaitlistStatus::Waiting);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isWaiting(): bool
    {
        return $this->status === WaitlistStatus::Waiting;
    }

    public function isNotified(): bool
    {
        return $this->status === WaitlistStatus::Notified;
    }

    public function isConverted(): bool
    {
        return $this->status === WaitlistStatus::Converted;
    }

    public function isExpired(): bool
    {
        return $this->status === WaitlistStatus::Expired || $this->expires_at < now();
    }

    public function isActive(): bool
    {
        return $this->status->isActive() && ! $this->isExpired();
    }

    public function hasUser(): bool
    {
        return $this->user_id !== null;
    }
}
