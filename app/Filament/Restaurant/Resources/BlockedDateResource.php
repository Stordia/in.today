<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\BlockedDateResource\Pages;
use App\Models\BlockedDate;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlockedDateResource extends Resource
{
    protected static ?string $model = BlockedDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Blocked Date';

    protected static ?string $pluralModelLabel = 'Blocked Dates';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', CurrentRestaurant::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Date & Time')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_all_day')
                            ->label('Block entire day')
                            ->default(true)
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Time Range')
                    ->schema([
                        Forms\Components\TimePicker::make('time_from')
                            ->label('From')
                            ->seconds(false)
                            ->requiredUnless('is_all_day', true),
                        Forms\Components\TimePicker::make('time_to')
                            ->label('To')
                            ->seconds(false)
                            ->requiredUnless('is_all_day', true),
                    ])
                    ->columns(2)
                    ->hidden(fn (Forms\Get $get): bool => $get('is_all_day')),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('e.g., Private event, Holiday, Renovation')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_all_day')
                    ->label('All Day')
                    ->boolean(),
                Tables\Columns\TextColumn::make('time_from')
                    ->label('From')
                    ->time('H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('time_to')
                    ->label('To')
                    ->time('H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->reason)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_all_day')
                    ->label('All Day'),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming only')
                    ->query(fn (Builder $query): Builder => $query->where('date', '>=', now()->toDateString()))
                    ->default(),
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
            ->defaultSort('date');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockedDates::route('/'),
            'create' => Pages\CreateBlockedDate::route('/create'),
            'edit' => Pages\EditBlockedDate::route('/{record}/edit'),
        ];
    }
}
