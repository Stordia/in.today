<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AffiliatePayoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_id',
        'amount',
        'currency',
        'status',
        'period_start',
        'period_end',
        'method',
        'reference',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AffiliatePayoutStatus::class,
            'amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class, 'affiliate_payout_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', AffiliatePayoutStatus::Pending);
    }

    public function scopePaid($query)
    {
        return $query->where('status', AffiliatePayoutStatus::Paid);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === AffiliatePayoutStatus::Pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === AffiliatePayoutStatus::Processing;
    }

    public function isPaid(): bool
    {
        return $this->status === AffiliatePayoutStatus::Paid;
    }

    public function isCancelled(): bool
    {
        return $this->status === AffiliatePayoutStatus::Cancelled;
    }

    public function getConversionsCountAttribute(): int
    {
        return $this->conversions()->count();
    }
}
