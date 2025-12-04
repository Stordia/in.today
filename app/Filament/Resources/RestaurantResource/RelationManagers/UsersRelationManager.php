<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantResource\RelationManagers;

use App\Enums\RestaurantRole;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Relation manager to display and manage users linked to a restaurant.
 *
 * Shows users who have access to this restaurant via RestaurantUser pivot.
 */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Team Members';

    protected static ?string $icon = 'heroicon-o-users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(fn () => User::query()
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $user) => [
                            $user->id => "{$user->name} ({$user->email})",
                        ]))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('role')
                    ->options(RestaurantRole::class)
                    ->required()
                    ->default(RestaurantRole::Staff),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!'),
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
                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('Access Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Linked')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Team Member')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('User'),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(RestaurantRole::class)
                            ->required()
                            ->default(RestaurantRole::Staff),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View User')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
                Tables\Actions\DetachAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ])
            // Must use fully qualified column name to avoid SQL ambiguity
            // (both users and restaurant_users tables have columns that could conflict)
            ->defaultSort('users.name');
    }
}
