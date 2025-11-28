<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Widgets;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingReservationsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming Reservations';

    public function table(Table $table): Table
    {
        $restaurantId = CurrentRestaurant::id();

        return $table
            ->query(
                Reservation::query()
                    ->when($restaurantId, fn (Builder $query) => $query->where('restaurant_id', $restaurantId))
                    ->when(! $restaurantId, fn (Builder $query) => $query->whereRaw('1 = 0'))
                    ->where('date', '>=', now()->toDateString())
                    ->whereNotIn('status', [
                        ReservationStatus::CancelledByCustomer,
                        ReservationStatus::CancelledByRestaurant,
                        ReservationStatus::NoShow,
                    ])
                    ->orderBy('date')
                    ->orderBy('time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('D, M j')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Guest')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guests')
                    ->label('Party')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state): string => match ($state) {
                        ReservationStatus::Pending => 'warning',
                        ReservationStatus::Confirmed => 'success',
                        ReservationStatus::Completed => 'info',
                        ReservationStatus::CancelledByCustomer, ReservationStatus::CancelledByRestaurant => 'danger',
                        ReservationStatus::NoShow => 'gray',
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (ReservationSource $state): string => match ($state) {
                        ReservationSource::Platform => 'primary',
                        ReservationSource::Widget => 'info',
                        ReservationSource::Phone => 'gray',
                        ReservationSource::WalkIn => 'warning',
                        ReservationSource::Api => 'success',
                    }),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No upcoming reservations')
            ->emptyStateDescription('New reservations will appear here.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
