<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\OpeningHourResource\Pages;
use App\Models\OpeningHour;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpeningHourResource extends Resource
{
    protected static ?string $model = OpeningHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Opening Hour';

    protected static ?string $pluralModelLabel = 'Opening Hours';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', CurrentRestaurant::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\Select::make('day_of_week')
                            ->options(OpeningHour::DAYS)
                            ->required(),
                        Forms\Components\TextInput::make('shift_name')
                            ->maxLength(255)
                            ->helperText('e.g., Lunch, Dinner, Brunch'),
                        Forms\Components\Toggle::make('is_open')
                            ->label('Open')
                            ->default(true)
                            ->live(),
                    ])->columns(3),

                Forms\Components\Section::make('Hours')
                    ->schema([
                        Forms\Components\TimePicker::make('open_time')
                            ->label('Opens at')
                            ->seconds(false)
                            ->required(fn (Forms\Get $get): bool => $get('is_open') === true)
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if ($get('is_open') && $value && $get('close_time')) {
                                        $openTime = \Carbon\Carbon::parse($value);
                                        $closeTime = \Carbon\Carbon::parse($get('close_time'));

                                        if ($closeTime->lte($openTime)) {
                                            $fail('Closing time must be after opening time.');
                                        }
                                    }
                                },
                            ]),
                        Forms\Components\TimePicker::make('close_time')
                            ->label('Closes at')
                            ->seconds(false)
                            ->required(fn (Forms\Get $get): bool => $get('is_open') === true),
                        Forms\Components\TimePicker::make('last_reservation_time')
                            ->label('Last reservation')
                            ->seconds(false)
                            ->helperText('Leave empty to use closing time'),
                    ])
                    ->columns(3)
                    ->visible(fn (Forms\Get $get): bool => $get('is_open') === true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state): string => OpeningHour::DAYS[$state] ?? 'Unknown')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift_name')
                    ->label('Shift')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('open_time')
                    ->label('Opens')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('close_time')
                    ->label('Closes')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('last_reservation_time')
                    ->label('Last Res.')
                    ->time('H:i')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_open')
                    ->label('Open')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_open')
                    ->label('Open'),
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Day')
                    ->options(OpeningHour::DAYS),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_open')
                    ->label(fn (OpeningHour $record): string => $record->is_open ? 'Mark Closed' : 'Mark Open')
                    ->icon(fn (OpeningHour $record): string => $record->is_open ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (OpeningHour $record): string => $record->is_open ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (OpeningHour $record): void {
                        if ($record->is_open) {
                            // Closing the day
                            $record->update(['is_open' => false]);
                            Notification::make()
                                ->success()
                                ->title('Day marked as closed')
                                ->body("{$record->getDayName()} is now closed.")
                                ->send();
                        } else {
                            // Opening the day - use existing times or defaults
                            $data = ['is_open' => true];
                            if (! $record->open_time) {
                                $data['open_time'] = '17:00';
                            }
                            if (! $record->close_time) {
                                $data['close_time'] = '23:00';
                            }
                            $record->update($data);
                            Notification::make()
                                ->success()
                                ->title('Day marked as open')
                                ->body("{$record->getDayName()} is now open.")
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('copy_to_days')
                    ->label('Copy to other days')
                    ->icon('heroicon-o-document-duplicate')
                    ->form(function (OpeningHour $record): array {
                        // Build checkbox list excluding the current day
                        $options = collect(OpeningHour::DAYS)
                            ->filter(fn ($label, $day) => $day !== $record->day_of_week)
                            ->all();

                        return [
                            Forms\Components\CheckboxList::make('target_days')
                                ->label('Copy this schedule to:')
                                ->options($options)
                                ->required()
                                ->minItems(1),
                        ];
                    })
                    ->action(function (OpeningHour $record, array $data): void {
                        $targetDays = $data['target_days'] ?? [];
                        $restaurantId = CurrentRestaurant::id();

                        foreach ($targetDays as $dayOfWeek) {
                            OpeningHour::updateOrCreate(
                                [
                                    'restaurant_id' => $restaurantId,
                                    'profile' => 'booking',
                                    'day_of_week' => $dayOfWeek,
                                    'shift_name' => $record->shift_name,
                                ],
                                [
                                    'is_open' => $record->is_open,
                                    'open_time' => $record->open_time,
                                    'close_time' => $record->close_time,
                                    'last_reservation_time' => $record->last_reservation_time,
                                ]
                            );
                        }

                        $dayNames = collect($targetDays)
                            ->map(fn ($day) => OpeningHour::DAYS[$day])
                            ->join(', ', ' and ');

                        Notification::make()
                            ->success()
                            ->title('Schedule copied')
                            ->body("Copied {$record->getDayName()} schedule to {$dayNames}.")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day_of_week');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpeningHours::route('/'),
            'create' => Pages\CreateOpeningHour::route('/create'),
            'edit' => Pages\EditOpeningHour::route('/{record}/edit'),
        ];
    }
}
