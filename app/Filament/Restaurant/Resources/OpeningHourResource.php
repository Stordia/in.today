<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\OpeningHourResource\Pages;
use App\Models\OpeningHour;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
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
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Hours')
                    ->schema([
                        Forms\Components\TimePicker::make('open_time')
                            ->label('Opens at')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\TimePicker::make('close_time')
                            ->label('Closes at')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\TimePicker::make('last_reservation_time')
                            ->label('Last reservation')
                            ->seconds(false)
                            ->helperText('Leave empty to use closing time'),
                    ])->columns(3),
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
