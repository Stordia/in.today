<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reservation Details')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->label('UUID')
                            ->disabled(),
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->disabled(),
                        Forms\Components\DatePicker::make('date')
                            ->disabled(),
                        Forms\Components\TextInput::make('time')
                            ->disabled(),
                        Forms\Components\TextInput::make('guests')
                            ->disabled(),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (min)')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options(ReservationStatus::class)
                            ->disabled(),
                        Forms\Components\Select::make('source')
                            ->options(ReservationSource::class)
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_email')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_phone')
                            ->disabled(),
                        Forms\Components\Textarea::make('customer_notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Internal')
                    ->schema([
                        Forms\Components\Textarea::make('internal_notes')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('language')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('confirmed_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guests')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state): string => match ($state) {
                        ReservationStatus::Pending => 'warning',
                        ReservationStatus::Confirmed => 'success',
                        ReservationStatus::Completed => 'info',
                        ReservationStatus::CancelledByCustomer, ReservationStatus::CancelledByRestaurant => 'danger',
                        ReservationStatus::NoShow => 'gray',
                    })
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('restaurant_id')
                    ->label('Restaurant')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->where('date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->where('date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewReservation::route('/{record}'),
        ];
    }
}
