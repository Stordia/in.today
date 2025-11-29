<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\ReservationResource\Pages;

use App\Enums\ReservationSource;
use App\Filament\Restaurant\Resources\ReservationResource;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    /**
     * Mount the page and pre-fill form data from query parameters.
     *
     * Supports: ?date=YYYY-MM-DD&time=HH:MM&guests=N
     */
    public function mount(): void
    {
        parent::mount();

        $request = request();
        $prefillData = [];

        // Prefill date from query parameter
        if ($request->has('date')) {
            $prefillData['date'] = $request->get('date');
        }

        // Prefill time from query parameter
        if ($request->has('time')) {
            $prefillData['time'] = $request->get('time');
        }

        // Prefill guests (party size) from query parameter
        if ($request->has('guests')) {
            $prefillData['guests'] = (int) $request->get('guests');
        }

        if (! empty($prefillData)) {
            $this->form->fill(array_merge(
                $this->form->getState(),
                $prefillData
            ));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = CurrentRestaurant::id();

        // Default source if not set
        if (empty($data['source'])) {
            $data['source'] = ReservationSource::Phone;
        }

        return $data;
    }
}
