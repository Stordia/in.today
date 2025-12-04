<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\RestaurantResource;
use App\Models\Restaurant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Relation manager to display restaurants linked to a user.
 *
 * Shows restaurants where the user has access via RestaurantUser pivot.
 */
class RestaurantsRelationManager extends RelationManager
{
    protected static string $relationship = 'restaurants';

    protected static ?string $title = 'Restaurants';

    protected static ?string $icon = 'heroicon-o-building-storefront';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'success',
                        'manager' => 'warning',
                        'staff' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('Access Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Restaurant Active'),
            ])
            ->headerActions([
                // No create action - restaurants are linked via OnboardRestaurant or manually
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Restaurant $record): string => RestaurantResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }
}
