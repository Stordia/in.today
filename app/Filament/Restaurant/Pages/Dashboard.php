<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Filament\Restaurant\Widgets\StatsOverviewWidget;
use App\Filament\Restaurant\Widgets\UpcomingReservationsWidget;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return "Dashboard - {$restaurant->name}";
    }

    public function getSubheading(): ?string
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'Please select a restaurant to view your dashboard.';
        }

        return 'Welcome to your business dashboard.';
    }

    public function getWidgets(): array
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return [];
        }

        return [
            StatsOverviewWidget::class,
            UpcomingReservationsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return [
                \Filament\Actions\Action::make('select_restaurant')
                    ->label('Select Restaurant')
                    ->icon('heroicon-o-building-storefront')
                    ->url(route('filament.business.pages.switch-restaurant'))
                    ->color('primary'),
            ];
        }

        return [];
    }
}
