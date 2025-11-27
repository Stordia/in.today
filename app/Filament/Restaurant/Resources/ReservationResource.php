<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Filament\Restaurant\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Models\Table;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', CurrentRestaurant::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reservation Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TextInput::make('guests')
                            ->label('Party Size')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(2),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (min)')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(480)
                            ->default(90),
                    ])->columns(4),

                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(50),
                    ])->columns(3),

                Forms\Components\Section::make('Table & Status')
                    ->schema([
                        Forms\Components\Select::make('table_id')
                            ->label('Table')
                            ->options(fn () => Table::query()
                                ->where('restaurant_id', CurrentRestaurant::id())
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('No table assigned'),
                        Forms\Components\Select::make('status')
                            ->options(ReservationStatus::class)
                            ->required()
                            ->default(ReservationStatus::Pending),
                        Forms\Components\Select::make('source')
                            ->options(ReservationSource::class)
                            ->required()
                            ->default(ReservationSource::Phone),
                    ])->columns(3),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Customer Notes')
                            ->rows(2)
                            ->helperText('Special requests from the customer'),
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(2)
                            ->helperText('Notes for staff (not visible to customer)'),
                    ])->columns(2),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('guests')
                    ->label('Guests')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table')
                    ->placeholder('—')
                    ->toggleable(),
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ReservationStatus::class),
                Tables\Filters\SelectFilter::make('source')
                    ->options(ReservationSource::class),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->where('date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->where('date', '<=', $date));
                    }),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->where('date', now()->toDateString())),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming')
                    ->query(fn (Builder $query): Builder => $query->where('date', '>=', now()->toDateString())->active())
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record): bool => $record->isPending())
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'status' => ReservationStatus::Confirmed,
                            'confirmed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('no_show')
                    ->label('No Show')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record): bool => $record->isConfirmed())
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'status' => ReservationStatus::NoShow,
                        ]);
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record): bool => $record->isConfirmed())
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'status' => ReservationStatus::Completed,
                            'completed_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
