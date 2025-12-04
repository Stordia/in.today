<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\GlobalRole;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Admin resource for managing Users/Customers.
 *
 * Provides platform admins with a view of all users, their roles,
 * and linked restaurants (for restaurant owners).
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Accounts & Customers';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isPlatformAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('global_role')
                            ->label('Role')
                            ->options(GlobalRole::class)
                            ->required()
                            ->default(GlobalRole::User),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied!'),
                Tables\Columns\TextColumn::make('role_badge')
                    ->label('Type')
                    ->badge()
                    ->getStateUsing(function (User $record): string {
                        if ($record->isPlatformAdmin()) {
                            return 'Platform Admin';
                        }

                        if ($record->restaurants_count > 0) {
                            return 'Restaurant Owner';
                        }

                        return 'User';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Platform Admin' => 'danger',
                        'Restaurant Owner' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('restaurants_count')
                    ->label('Restaurants')
                    ->counts('restaurants')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_restaurants')
                    ->label('Has Restaurants')
                    ->placeholder('All users')
                    ->trueLabel('With restaurants')
                    ->falseLabel('Without restaurants')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('restaurants'),
                        false: fn (Builder $query) => $query->whereDoesntHave('restaurants'),
                    ),
                Tables\Filters\TernaryFilter::make('is_platform_admin')
                    ->label('Platform Admins')
                    ->placeholder('All users')
                    ->trueLabel('Admins only')
                    ->falseLabel('Non-admins only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('global_role', GlobalRole::PlatformAdmin),
                        false: fn (Builder $query) => $query->where('global_role', '!=', GlobalRole::PlatformAdmin),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RestaurantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
