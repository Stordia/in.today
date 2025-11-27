<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Restaurant\Pages\Dashboard;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class RestaurantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('business')
            ->path('business')
            ->login()
            ->brandName('in.today Business')
            ->renderHook(
                'panels::head.end',
                fn () => '<meta name="robots" content="noindex, nofollow">'
            )
            ->colors([
                'primary' => Color::Sky,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Green,
                'info' => Color::Cyan,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Operations')
                    ->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make()
                    ->label('Bookings')
                    ->icon('heroicon-o-calendar-days'),
            ])
            ->userMenuItems([
                'restaurant-switcher' => MenuItem::make()
                    ->label(fn () => static::getCurrentRestaurantLabel())
                    ->icon('heroicon-o-building-storefront')
                    ->url(fn () => route('filament.business.pages.switch-restaurant'))
                    ->visible(fn () => static::hasMultipleRestaurants()),
            ])
            ->discoverResources(in: app_path('Filament/Restaurant/Resources'), for: 'App\\Filament\\Restaurant\\Resources')
            ->discoverPages(in: app_path('Filament/Restaurant/Pages'), for: 'App\\Filament\\Restaurant\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Restaurant/Widgets'), for: 'App\\Filament\\Restaurant\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }

    protected static function getCurrentRestaurantLabel(): string
    {
        $restaurant = CurrentRestaurant::get();

        return $restaurant ? "Switch: {$restaurant->name}" : 'Switch Restaurant';
    }

    protected static function hasMultipleRestaurants(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return CurrentRestaurant::getUserRestaurants($user)->count() > 1;
    }
}
