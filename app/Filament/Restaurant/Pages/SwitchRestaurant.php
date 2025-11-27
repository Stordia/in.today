<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Models\Restaurant;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SwitchRestaurant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string $view = 'filament.restaurant.pages.switch-restaurant';

    protected static ?string $title = 'Switch Restaurant';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $restaurant_id = null;

    public function mount(): void
    {
        $this->restaurant_id = CurrentRestaurant::id();
    }

    public function form(Form $form): Form
    {
        $restaurants = CurrentRestaurant::getUserRestaurants(Auth::user());

        return $form
            ->schema([
                Select::make('restaurant_id')
                    ->label('Select Restaurant')
                    ->options($restaurants->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->default($this->restaurant_id),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (! CurrentRestaurant::userHasAccess($data['restaurant_id'])) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have access to this restaurant.')
                ->danger()
                ->send();

            return;
        }

        CurrentRestaurant::set($data['restaurant_id']);

        $restaurant = Restaurant::find($data['restaurant_id']);

        Notification::make()
            ->title('Restaurant Switched')
            ->body("You are now managing: {$restaurant->name}")
            ->success()
            ->send();

        $this->redirect(route('filament.business.pages.dashboard'));
    }

    public static function getSlug(): string
    {
        return 'switch-restaurant';
    }
}
