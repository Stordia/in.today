<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Filament\Restaurant\Resources\ReservationResource\Pages;
use App\Mail\ReservationCustomerStatusUpdate;
use App\Models\Reservation;
use App\Models\Table;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                    ->label('Date')
                    ->date('D, M j')
                    ->sortable()
                    ->description(fn (Reservation $record): string => $record->date->isToday() ? 'Today' : ($record->date->isTomorrow() ? 'Tomorrow' : '')),
                Tables\Columns\TextColumn::make('time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Reservation $record): ?string => $record->customer_phone),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('guests')
                    ->label('Guests')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-users')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table')
                    ->placeholder('—')
                    ->toggleable()
                    ->icon('heroicon-o-table-cells'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state): string => match ($state) {
                        ReservationStatus::Pending => 'warning',
                        ReservationStatus::Confirmed => 'success',
                        ReservationStatus::Completed => 'info',
                        ReservationStatus::CancelledByCustomer, ReservationStatus::CancelledByRestaurant => 'danger',
                        ReservationStatus::NoShow => 'gray',
                    })
                    ->icon(fn (ReservationStatus $state): string => match ($state) {
                        ReservationStatus::Pending => 'heroicon-o-clock',
                        ReservationStatus::Confirmed => 'heroicon-o-check-circle',
                        ReservationStatus::Completed => 'heroicon-o-check-badge',
                        ReservationStatus::CancelledByCustomer, ReservationStatus::CancelledByRestaurant => 'heroicon-o-x-circle',
                        ReservationStatus::NoShow => 'heroicon-o-user-minus',
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (ReservationSource $state): string => match ($state) {
                        ReservationSource::Widget => 'success',
                        ReservationSource::Platform => 'info',
                        ReservationSource::Phone => 'gray',
                        ReservationSource::WalkIn => 'gray',
                        ReservationSource::Api => 'primary',
                    })
                    ->icon(fn (ReservationSource $state): string => match ($state) {
                        ReservationSource::Widget => 'heroicon-o-globe-alt',
                        ReservationSource::Platform => 'heroicon-o-building-office',
                        ReservationSource::Phone => 'heroicon-o-phone',
                        ReservationSource::WalkIn => 'heroicon-o-user',
                        ReservationSource::Api => 'heroicon-o-code-bracket',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('time_period')
                    ->label('Time Period')
                    ->placeholder('All reservations')
                    ->trueLabel('Upcoming only')
                    ->falseLabel('Past only')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('date', '>=', now()->toDateString()),
                        false: fn (Builder $query): Builder => $query->where('date', '<', now()->toDateString()),
                        blank: fn (Builder $query): Builder => $query,
                    )
                    ->default(true),
                Tables\Filters\Filter::make('today')
                    ->label('Today only')
                    ->query(fn (Builder $query): Builder => $query->where('date', now()->toDateString()))
                    ->toggle(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(ReservationStatus::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options(ReservationSource::class)
                    ->multiple(),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until date'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->where('date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->where('date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . \Carbon\Carbon::parse($data['from'])->format('M j, Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . \Carbon\Carbon::parse($data['until'])->format('M j, Y');
                        }

                        return $indicators;
                    }),
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

                        // Send confirmation email to customer
                        if (! empty($record->customer_email)) {
                            try {
                                $record->load('restaurant');
                                Mail::to($record->customer_email)
                                    ->send(new ReservationCustomerStatusUpdate($record, $record->restaurant, 'confirmed'));
                            } catch (\Throwable $e) {
                                Log::warning('Failed to send reservation confirmation email', [
                                    'reservation_id' => $record->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
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
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Reservation')
                    ->modalDescription('Are you sure you want to cancel this reservation? The customer will be notified via email.')
                    ->visible(fn (Reservation $record): bool => $record->isPending() || $record->isConfirmed())
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'status' => ReservationStatus::CancelledByRestaurant,
                            'cancelled_at' => now(),
                        ]);

                        // Send cancellation email to customer
                        if (! empty($record->customer_email)) {
                            try {
                                $record->load('restaurant');
                                Mail::to($record->customer_email)
                                    ->send(new ReservationCustomerStatusUpdate($record, $record->restaurant, 'cancelled'));
                            } catch (\Throwable $e) {
                                Log::warning('Failed to send reservation cancellation email', [
                                    'reservation_id' => $record->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
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
