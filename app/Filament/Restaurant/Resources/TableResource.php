<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\TableResource\Pages;
use App\Models\Table;
use App\Support\Tenancy\CurrentRestaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;

class TableResource extends Resource
{
    protected static ?string $model = Table::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Table';

    protected static ?string $pluralModelLabel = 'Tables';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', CurrentRestaurant::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Table Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('e.g., Table 1, Patio A, VIP Booth'),
                        Forms\Components\TextInput::make('zone')
                            ->maxLength(255)
                            ->helperText('e.g., Main Floor, Terrace, Bar Area'),
                        Forms\Components\TextInput::make('seats')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(4),
                    ])->columns(3),

                Forms\Components\Section::make('Guest Capacity')
                    ->schema([
                        Forms\Components\TextInput::make('min_guests')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Minimum guests for this table'),
                        Forms\Components\TextInput::make('max_guests')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Maximum guests (leave empty for no limit)'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive tables will not be available for reservations'),
                        Forms\Components\Toggle::make('is_combinable')
                            ->label('Can be combined')
                            ->default(false)
                            ->helperText('Can this table be combined with others for larger parties?'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order'),
                    ])->columns(3),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('seats')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_guests')
                    ->label('Min')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_guests')
                    ->label('Max')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_combinable')
                    ->label('Combinable')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('zone')
                    ->options(fn () => Table::query()
                        ->where('restaurant_id', CurrentRestaurant::id())
                        ->distinct()
                        ->pluck('zone', 'zone')
                        ->filter()
                        ->toArray()),
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
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }
}
