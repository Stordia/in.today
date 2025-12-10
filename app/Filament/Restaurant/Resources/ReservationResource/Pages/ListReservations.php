<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\ReservationResource\Pages;

use App\Filament\Restaurant\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Disable query string tracking for table filters.
     * Filters will be persisted in session instead.
     */
    protected function getTableQueryStringIdentifier(): ?string
    {
        return null;
    }

    /**
     * Persist filter state in session for better UX.
     * - No cluttered URLs
     * - Filters survive page refreshes within session
     * - Default "Upcoming only" applies on first visit
     */
    public function getTableFiltersSessionKey(): string
    {
        return 'reservation-list-filters';
    }
}
