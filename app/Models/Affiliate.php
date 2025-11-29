<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AffiliateConversionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_name',
        'contact_email',
        'type',
        'status',
        'default_commission_rate',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_commission_rate' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function links(): HasMany
    {
        return $this->hasMany(AffiliateLink::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /*
    |--------------------------------------------------------------------------
    | Commission Aggregates
    |--------------------------------------------------------------------------
    */

    public function getTotalConversionsAttribute(): int
    {
        return $this->conversions()->count();
    }

    public function getPendingConversionsCountAttribute(): int
    {
        return $this->conversions()
            ->where('status', AffiliateConversionStatus::Pending)
            ->count();
    }

    public function getTotalApprovedCommissionAttribute(): float
    {
        return (float) $this->conversions()
            ->where('status', AffiliateConversionStatus::Approved)
            ->sum('commission_amount');
    }

    public function getTotalPaidCommissionAttribute(): float
    {
        return (float) $this->conversions()
            ->where('status', AffiliateConversionStatus::Paid)
            ->sum('commission_amount');
    }

    public function getTotalPendingCommissionAttribute(): float
    {
        return (float) $this->conversions()
            ->where('status', AffiliateConversionStatus::Pending)
            ->sum('commission_amount');
    }
}
