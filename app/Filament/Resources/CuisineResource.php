<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CuisineResource\Pages;
use App\Models\Cuisine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CuisineResource extends Resource
{
    protected static ?string $model = Cuisine::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = 'Directory';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Names (Multilingual)')
                    ->schema([
                        Forms\Components\TextInput::make('name_en')
                            ->label('English Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_de')
                            ->label('German Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_el')
                            ->label('Greek Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_it')
                            ->label('Italian Name')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Leave blank to auto-generate from English name'),
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->helperText('Icon identifier (e.g., emoji or icon class)'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('')
                    ->width(40),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('English')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_de')
                    ->label('German')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name_el')
                    ->label('Greek')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name_it')
                    ->label('Italian')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('restaurants_count')
                    ->label('Restaurants')
                    ->counts('restaurants')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCuisines::route('/'),
            'create' => Pages\CreateCuisine::route('/create'),
            'edit' => Pages\EditCuisine::route('/{record}/edit'),
        ];
    }
}
