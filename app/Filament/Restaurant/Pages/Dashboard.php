<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Support\Tenancy\CurrentRestaurant;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getHeading(): string
    {
        $restaurant = CurrentRestaurant::get();

        return $restaurant
            ? "Dashboard - {$restaurant->name}"
            : 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Welcome to your business dashboard.';
    }
}
