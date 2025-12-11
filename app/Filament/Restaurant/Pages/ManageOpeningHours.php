<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Pages;

use App\Models\BlockedDate;
use App\Models\OpeningHour;
use App\Support\Tenancy\CurrentRestaurant;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

/**
 * Manage Opening Hours & Special Dates for the booking system.
 *
 * This page allows restaurant owners to configure:
 * - Weekly opening hours for online bookings (Booking Hours profile)
 * - Special dates and blocked dates (holidays, private events, etc.)
 */
class ManageOpeningHours extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Opening Hours';

    protected static ?string $title = 'Opening Hours & Special Dates';

    protected static string $view = 'filament.restaurant.pages.manage-opening-hours';

    public ?array $data = [];

    public function mount(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            $this->data = [];

            return;
        }

        // Load weekly opening hours (booking profile)
        $weeklyHours = [];
        for ($day = 0; $day <= 6; $day++) {
            $hours = OpeningHour::query()
                ->where('restaurant_id', $restaurant->id)
                ->bookingProfile()
                ->forDay($day)
                ->first();

            if ($hours) {
                $weeklyHours["day_{$day}_is_open"] = $hours->is_open;
                $weeklyHours["day_{$day}_open_time"] = $hours->open_time instanceof Carbon
                    ? $hours->open_time->format('H:i')
                    : $hours->open_time;
                $weeklyHours["day_{$day}_close_time"] = $hours->close_time instanceof Carbon
                    ? $hours->close_time->format('H:i')
                    : $hours->close_time;
            } else {
                $weeklyHours["day_{$day}_is_open"] = false;
                $weeklyHours["day_{$day}_open_time"] = '12:00';
                $weeklyHours["day_{$day}_close_time"] = '22:00';
            }
        }

        // Load blocked dates (booking profile)
        $blockedDates = BlockedDate::query()
            ->where('restaurant_id', $restaurant->id)
            ->bookingProfile()
            ->upcoming()
            ->orderBy('date')
            ->get()
            ->map(fn (BlockedDate $blocked) => [
                'id' => $blocked->id,
                'date' => $blocked->date instanceof Carbon ? $blocked->date->toDateString() : $blocked->date,
                'reason' => $blocked->reason,
            ])
            ->toArray();

        $this->form->fill([
            ...$weeklyHours,
            'blocked_dates' => $blockedDates,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            return 'No Restaurant Selected';
        }

        return 'Opening Hours & Special Dates';
    }

    public function getSubheading(): ?string
    {
        return 'Manage the hours when guests can book online, plus holidays and special dates.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Weekly Booking Hours')
                    ->description('Set the hours when guests can make online reservations. These hours control slot availability on your public booking page.')
                    ->schema($this->getWeeklyHoursSchema())
                    ->columns(1),

                Section::make('Special Dates & Holidays')
                    ->description('Block specific dates when you cannot accept bookings (holidays, private events, renovations, etc.).')
                    ->schema([
                        Repeater::make('blocked_dates')
                            ->label('Blocked Dates')
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Date')
                                    ->required()
                                    ->minDate(now())
                                    ->native(false),

                                Textarea::make('reason')
                                    ->label('Reason (optional)')
                                    ->rows(2)
                                    ->maxLength(255)
                                    ->placeholder('e.g., Christmas Holiday, Private Event, Renovation'),
                            ])
                            ->columns(2)
                            ->reorderable(false)
                            ->addActionLabel('Add Blocked Date')
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    private function getWeeklyHoursSchema(): array
    {
        $days = [
            0 => 'Monday',
            1 => 'Tuesday',
            2 => 'Wednesday',
            3 => 'Thursday',
            4 => 'Friday',
            5 => 'Saturday',
            6 => 'Sunday',
        ];

        $schema = [];

        foreach ($days as $dayNum => $dayName) {
            $schema[] = Section::make($dayName)
                ->description("Hours when guests can start a booking on {$dayName}")
                ->schema([
                    Toggle::make("day_{$dayNum}_is_open")
                        ->label('Open for bookings')
                        ->default(false)
                        ->live(),

                    TimePicker::make("day_{$dayNum}_open_time")
                        ->label('Opening Time')
                        ->seconds(false)
                        ->visible(fn ($get) => $get("day_{$dayNum}_is_open"))
                        ->required(fn ($get) => $get("day_{$dayNum}_is_open")),

                    TimePicker::make("day_{$dayNum}_close_time")
                        ->label('Closing Time')
                        ->seconds(false)
                        ->visible(fn ($get) => $get("day_{$dayNum}_is_open"))
                        ->required(fn ($get) => $get("day_{$dayNum}_is_open")),
                ])
                ->columns(3)
                ->collapsible()
                ->collapsed(fn ($get) => ! $get("day_{$dayNum}_is_open"));
        }

        return $schema;
    }

    public function save(): void
    {
        $restaurant = CurrentRestaurant::get();

        if (! $restaurant) {
            Notification::make()
                ->title('Error')
                ->body('No restaurant selected.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($restaurant, $data) {
                // Update weekly hours
                for ($day = 0; $day <= 6; $day++) {
                    $isOpen = $data["day_{$day}_is_open"] ?? false;
                    $openTime = $data["day_{$day}_open_time"] ?? '12:00';
                    $closeTime = $data["day_{$day}_close_time"] ?? '22:00';

                    OpeningHour::updateOrCreate(
                        [
                            'restaurant_id' => $restaurant->id,
                            'profile' => 'booking',
                            'day_of_week' => $day,
                        ],
                        [
                            'is_open' => $isOpen,
                            'open_time' => $openTime,
                            'close_time' => $closeTime,
                        ]
                    );
                }

                // Sync blocked dates
                // Remove all existing upcoming blocked dates for booking profile
                BlockedDate::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->bookingProfile()
                    ->where('date', '>=', now()->toDateString())
                    ->delete();

                // Create new blocked dates
                if (! empty($data['blocked_dates'])) {
                    foreach ($data['blocked_dates'] as $blocked) {
                        if (empty($blocked['date'])) {
                            continue;
                        }

                        BlockedDate::create([
                            'restaurant_id' => $restaurant->id,
                            'profile' => 'booking',
                            'date' => $blocked['date'],
                            'is_all_day' => true,
                            'reason' => $blocked['reason'] ?? null,
                        ]);
                    }
                }
            });

            Notification::make()
                ->title('Saved Successfully')
                ->body('Your opening hours and special dates have been updated.')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Save Failed')
                ->body('An error occurred while saving. Please try again.')
                ->danger()
                ->send();

            throw $e;
        }
    }

    public function hasRestaurant(): bool
    {
        return CurrentRestaurant::get() !== null;
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
