<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\WaitlistStatus;
use App\Filament\Restaurant\Resources\WaitlistResource\Pages;
use App\Models\Waitlist;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaitlistResource extends Resource
{
    protected static ?string $model = Waitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Waitlist Entry';

    protected static ?string $pluralModelLabel = 'Waitlist';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', CurrentRestaurant::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('preferred_time')
                            ->label('Preferred Time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TextInput::make('guests')
                            ->label('Party Size')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(2),
                    ])->columns(3),

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

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(WaitlistStatus::class)
                            ->required()
                            ->default(WaitlistStatus::Waiting),
                        Forms\Components\DateTimePicker::make('notified_at')
                            ->label('Notified At')
                            ->helperText('When the customer was notified of availability'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->helperText('When this waitlist entry expires'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('preferred_time')
                    ->label('Time')
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (WaitlistStatus $state): string => match ($state) {
                        WaitlistStatus::Waiting => 'warning',
                        WaitlistStatus::Notified => 'info',
                        WaitlistStatus::Converted => 'success',
                        WaitlistStatus::Expired => 'gray',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(WaitlistStatus::class),
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
                Tables\Filters\Filter::make('active')
                    ->label('Active Only')
                    ->query(fn (Builder $query): Builder => $query->active())
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('notify')
                    ->label('Notify')
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Waitlist $record): bool => $record->isWaiting())
                    ->action(function (Waitlist $record): void {
                        $record->update([
                            'status' => WaitlistStatus::Notified,
                            'notified_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('convert')
                    ->label('Convert to Reservation')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (Waitlist $record): bool => $record->isActive())
                    ->url(fn (Waitlist $record): string => route('filament.business.resources.reservations.create', [
                        'date' => $record->date?->format('Y-m-d'),
                        'time' => $record->preferred_time?->format('H:i'),
                        'guests' => $record->guests,
                        'customer_name' => $record->customer_name,
                        'customer_email' => $record->customer_email,
                        'customer_phone' => $record->customer_phone,
                    ])),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaitlists::route('/'),
            'create' => Pages\CreateWaitlist::route('/create'),
            'edit' => Pages\EditWaitlist::route('/{record}/edit'),
        ];
    }
}
