<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Filament\Restaurant\Resources\ReservationResource;
use App\Services\Reservations\AvailabilityResult;
use App\Services\Reservations\AvailabilityService;
use App\Support\Tenancy\CurrentRestaurant;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CheckAvailability extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Availability';

    protected static ?string $title = 'Check Availability';

    protected static string $view = 'filament.restaurant.pages.check-availability';

    public ?string $date = null;

    public int $party_size = 2;

    public ?AvailabilityResult $availabilityResult = null;

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->party_size = 2;
    }

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return 'Check Availability';
    }

    public function getSubheading(): ?string
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'Please select a restaurant to check availability.';
        }

        return "Check available time slots for {$restaurant->name}";
    }

    protected function getHeaderActions(): array
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return [
                Action::make('select_restaurant')
                    ->label('Select Restaurant')
                    ->icon('heroicon-o-building-storefront')
                    ->url(route('filament.business.pages.switch-restaurant'))
                    ->color('primary'),
            ];
        }

        return [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false)
                    ->minDate(now()->startOfDay())
                    ->default(now()),
                TextInput::make('party_size')
                    ->label('Party Size')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(50)
                    ->default(2),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function checkAvailability(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return;
        }

        $service = app(AvailabilityService::class);

        $this->availabilityResult = $service->getAvailableTimeSlots(
            restaurant: $restaurant,
            date: CarbonImmutable::parse($this->date),
            partySize: $this->party_size,
        );
    }

    public function getSlots(): array
    {
        if (! $this->availabilityResult) {
            return [];
        }

        return $this->availabilityResult->slots;
    }

    public function hasRestaurant(): bool
    {
        return CurrentRestaurant::get() !== null;
    }

    public function getCreateReservationUrl(string $time): string
    {
        return ReservationResource::getUrl('create', [
            'date' => $this->date,
            'time' => $time,
            'guests' => $this->party_size,
        ]);
    }
}
