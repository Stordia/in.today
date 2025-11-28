<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Widgets;

use App\Enums\ReservationStatus;
use App\Enums\WaitlistStatus;
use App\Models\Reservation;
use App\Models\Waitlist;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $restaurantId = CurrentRestaurant::id();

        if (! $restaurantId) {
            return [];
        }

        return [
            $this->getTodaysReservationsStat($restaurantId),
            $this->getUpcomingReservationsStat($restaurantId),
            $this->getWaitlistStat($restaurantId),
        ];
    }

    private function getTodaysReservationsStat(int $restaurantId): Stat
    {
        $today = $this->getToday();

        $count = Reservation::query()
            ->where('restaurant_id', $restaurantId)
            ->where('date', $today)
            ->whereIn('status', [
                ReservationStatus::Pending,
                ReservationStatus::Confirmed,
                ReservationStatus::Completed,
            ])
            ->count();

        return Stat::make("Today's Reservations", $count)
            ->description('booked for today')
            ->descriptionIcon('heroicon-o-calendar')
            ->color('primary');
    }

    private function getUpcomingReservationsStat(int $restaurantId): Stat
    {
        $today = $this->getToday();
        $nextWeek = $today->copy()->addDays(7);

        $count = Reservation::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('date', [$today, $nextWeek])
            ->whereNotIn('status', [
                ReservationStatus::CancelledByCustomer,
                ReservationStatus::CancelledByRestaurant,
                ReservationStatus::NoShow,
            ])
            ->count();

        return Stat::make('Upcoming', $count)
            ->description('next 7 days')
            ->descriptionIcon('heroicon-o-arrow-trending-up')
            ->color('success');
    }

    private function getWaitlistStat(int $restaurantId): Stat
    {
        $count = Waitlist::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', [
                WaitlistStatus::Waiting,
                WaitlistStatus::Notified,
            ])
            ->count();

        return Stat::make('Waitlist', $count)
            ->description('guests waiting')
            ->descriptionIcon('heroicon-o-clock')
            ->color($count > 0 ? 'warning' : 'gray');
    }

    private function getToday(): Carbon
    {
        // TODO: Use restaurant timezone if available
        return now()->startOfDay();
    }
}
