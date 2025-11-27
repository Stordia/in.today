<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\WaitlistStatus;
use App\Filament\Resources\WaitlistResource\Pages;
use App\Models\Waitlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WaitlistResource extends Resource
{
    protected static ?string $model = Waitlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralModelLabel = 'Waitlist Entries';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Waitlist Details')
                    ->schema([
                        Forms\Components\Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->disabled(),
                        Forms\Components\DatePicker::make('date')
                            ->disabled(),
                        Forms\Components\TextInput::make('preferred_time')
                            ->disabled(),
                        Forms\Components\TextInput::make('guests')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options(WaitlistStatus::class)
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
                    ])->columns(3),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('notified_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('preferred_time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guests')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (WaitlistStatus $state): string => match ($state) {
                        WaitlistStatus::Waiting => 'warning',
                        WaitlistStatus::Notified => 'info',
                        WaitlistStatus::Converted => 'success',
                        WaitlistStatus::Expired => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
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
            'view' => Pages\ViewWaitlist::route('/{record}'),
        ];
    }
}
