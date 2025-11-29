<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AffiliateConversionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'affiliate_link_id',
        'restaurant_id',
        'contact_lead_id',
        'status',
        'commission_amount',
        'currency',
        'occurred_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => AffiliateConversionStatus::class,
            'commission_amount' => 'decimal:2',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
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

    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function contactLead(): BelongsTo
    {
        return $this->belongsTo(ContactLead::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByStatus($query, AffiliateConversionStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', AffiliateConversionStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', AffiliateConversionStatus::Approved);
    }

    public function scopePaid($query)
    {
        return $query->where('status', AffiliateConversionStatus::Paid);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', AffiliateConversionStatus::Rejected);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === AffiliateConversionStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === AffiliateConversionStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === AffiliateConversionStatus::Rejected;
    }

    public function isPaid(): bool
    {
        return $this->status === AffiliateConversionStatus::Paid;
    }
}
