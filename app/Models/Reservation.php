<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatus;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'uuid',
        'customer_name',
        'customer_email',
        'customer_phone',
        'date',
        'time',
        'guests',
        'duration_minutes',
        'table_id',
        'status',
        'source',
        'customer_notes',
        'internal_notes',
        'language',
        'ip_address',
        'user_agent',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
        // Deposit fields
        'deposit_required',
        'deposit_amount',
        'deposit_currency',
        'deposit_status',
        'deposit_notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'time' => 'datetime:H:i',
            'guests' => 'integer',
            'duration_minutes' => 'integer',
            'status' => ReservationStatus::class,
            'source' => ReservationSource::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            // Deposit fields
            'deposit_required' => 'boolean',
            'deposit_amount' => 'decimal:2',
            'deposit_status' => DepositStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if (empty($reservation->uuid)) {
                $reservation->uuid = (string) Str::uuid();
            }
        });
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

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
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

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeByStatus($query, ReservationStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ReservationStatus::Pending,
            ReservationStatus::Confirmed,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', ReservationStatus::Pending);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', ReservationStatus::Confirmed);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->active()
            ->orderBy('date')
            ->orderBy('time');
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('customer_email', $email);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === ReservationStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === ReservationStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status->isCancelled();
    }

    public function isCompleted(): bool
    {
        return $this->status === ReservationStatus::Completed;
    }

    public function isNoShow(): bool
    {
        return $this->status === ReservationStatus::NoShow;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function hasTable(): bool
    {
        return $this->table_id !== null;
    }

    public function hasUser(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Get the calculated end time based on duration.
     */
    public function getEndTime(): string
    {
        return $this->time->copy()->addMinutes($this->duration_minutes)->format('H:i');
    }

    /*
    |--------------------------------------------------------------------------
    | Deposit Helpers
    |--------------------------------------------------------------------------
    */

    public function hasDepositRequired(): bool
    {
        return $this->deposit_required ?? false;
    }

    public function isDepositPending(): bool
    {
        return $this->deposit_status === DepositStatus::Pending;
    }

    public function isDepositPaid(): bool
    {
        return $this->deposit_status === DepositStatus::Paid;
    }

    public function isDepositWaived(): bool
    {
        return $this->deposit_status === DepositStatus::Waived;
    }

    /**
     * Get formatted deposit amount with currency.
     */
    public function getFormattedDepositAmount(): ?string
    {
        if (! $this->deposit_required || $this->deposit_amount === null) {
            return null;
        }

        $currency = $this->deposit_currency ?? 'EUR';

        return number_format((float) $this->deposit_amount, 2, ',', '.') . ' ' . $currency;
    }

    /**
     * Mark deposit as paid.
     */
    public function markDepositPaid(?string $notes = null): void
    {
        $this->update([
            'deposit_status' => DepositStatus::Paid,
            'deposit_notes' => $notes ?? $this->deposit_notes,
        ]);
    }

    /**
     * Waive deposit requirement.
     */
    public function waiveDeposit(?string $notes = null): void
    {
        $this->update([
            'deposit_status' => DepositStatus::Waived,
            'deposit_notes' => $notes ?? $this->deposit_notes,
        ]);
    }
}
