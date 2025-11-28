<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactLeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactLead extends Model
{
    protected $table = 'contact_leads';

    public const TYPE_OPTIONS = [
        'restaurant' => 'Restaurant',
        'cafe' => 'Café / Bistro',
        'bar' => 'Bar / Lounge',
        'hotel' => 'Hotel / Guesthouse',
        'catering' => 'Catering',
        'other' => 'Other',
    ];

    protected $fillable = [
        'locale',
        'name',
        'email',
        'phone',
        'restaurant_name',
        'city',
        'country',
        'website_url',
        'type',
        'services',
        'budget',
        'message',
        'source_url',
        'ip_address',
        'user_agent',
        'status',
        'assigned_to_user_id',
        'internal_notes',
        'restaurant_id',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'status' => ContactLeadStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ContactLeadEmail::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByStatus($query, ContactLeadStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            ContactLeadStatus::New,
            ContactLeadStatus::Contacted,
            ContactLeadStatus::Qualified,
            ContactLeadStatus::ProposalSent,
        ]);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', [
            ContactLeadStatus::Won,
            ContactLeadStatus::Lost,
            ContactLeadStatus::Spam,
        ]);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isNew(): bool
    {
        return $this->status === ContactLeadStatus::New;
    }

    public function isWon(): bool
    {
        return $this->status === ContactLeadStatus::Won;
    }

    public function isConverted(): bool
    {
        return $this->restaurant_id !== null;
    }

    public function canConvert(): bool
    {
        return ! $this->isConverted() && $this->status !== ContactLeadStatus::Won;
    }

    public function getLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->country]);

        return implode(', ', $parts);
    }

    public function getServicesListAttribute(): string
    {
        if (empty($this->services)) {
            return '';
        }

        return implode(', ', $this->services);
    }
}
