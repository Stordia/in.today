<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactLeadResource\Pages;

use App\Enums\ContactLeadStatus;
use App\Enums\GlobalRole;
use App\Enums\RestaurantPlan;
use App\Enums\RestaurantRole;
use App\Filament\Resources\ContactLeadResource;
use App\Models\City;
use App\Models\ContactLead;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EditContactLead extends EditRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('convert_to_restaurant')
                ->label('Convert to Restaurant')
                ->icon('heroicon-o-building-storefront')
                ->color('success')
                ->visible(fn (ContactLead $record): bool => $record->canConvert())
                ->form([
                    Forms\Components\Section::make('Restaurant Details')
                        ->schema([
                            Forms\Components\TextInput::make('restaurant_name')
                                ->label('Restaurant Name')
                                ->required()
                                ->default(fn (ContactLead $record): ?string => $record->restaurant_name)
                                ->maxLength(255),
                            Forms\Components\Select::make('city_id')
                                ->label('City')
                                ->options(City::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->helperText(fn (ContactLead $record): string => $record->city
                                    ? "Lead's city: {$record->city}, {$record->country}"
                                    : 'Select a city from the database'
                                ),
                            Forms\Components\Select::make('plan')
                                ->label('Plan')
                                ->options(RestaurantPlan::class)
                                ->default(RestaurantPlan::Starter)
                                ->required(),
                            Forms\Components\TextInput::make('timezone')
                                ->default('Europe/Berlin')
                                ->required()
                                ->maxLength(100),
                        ])->columns(2),

                    Forms\Components\Section::make('Owner Account')
                        ->description('A new user account will be created for the restaurant owner.')
                        ->schema([
                            Forms\Components\TextInput::make('owner_name')
                                ->label('Owner Name')
                                ->required()
                                ->default(fn (ContactLead $record): ?string => $record->name)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('owner_email')
                                ->label('Owner Email')
                                ->email()
                                ->required()
                                ->default(fn (ContactLead $record): ?string => $record->email)
                                ->maxLength(255),
                        ])->columns(2),
                ])
                ->action(function (ContactLead $record, array $data): void {
                    $this->convertLeadToRestaurant($record, $data);
                })
                ->modalHeading('Convert Lead to Restaurant')
                ->modalDescription('This will create a new restaurant and owner account, then link this lead to the restaurant.')
                ->modalSubmitActionLabel('Convert'),

            Actions\DeleteAction::make(),
        ];
    }

    private function convertLeadToRestaurant(ContactLead $record, array $data): void
    {
        // Check if email already exists
        $existingUser = User::where('email', $data['owner_email'])->first();

        if ($existingUser) {
            Notification::make()
                ->title('Email already exists')
                ->body("A user with email {$data['owner_email']} already exists. Please use a different email.")
                ->danger()
                ->send();

            return;
        }

        DB::transaction(function () use ($record, $data) {
            // Generate secure password
            $password = Str::random(16);

            // Create user account
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => $password,
                'global_role' => GlobalRole::User,
                'email_verified_at' => now(),
            ]);

            // Create restaurant
            $restaurant = Restaurant::create([
                'name' => $data['restaurant_name'],
                'slug' => Str::slug($data['restaurant_name']),
                'city_id' => $data['city_id'],
                'timezone' => $data['timezone'],
                'country' => $record->country,
                'plan' => $data['plan'],
                'is_active' => true,
                'is_verified' => false,
                'is_featured' => false,
            ]);

            // Create restaurant user (owner)
            RestaurantUser::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => RestaurantRole::Owner,
                'is_active' => true,
            ]);

            // Update lead
            $record->update([
                'restaurant_id' => $restaurant->id,
                'status' => ContactLeadStatus::Won,
                'assigned_to_user_id' => $record->assigned_to_user_id ?? Auth::id(),
                'internal_notes' => $this->appendNote(
                    $record->internal_notes,
                    "Converted to restaurant '{$restaurant->name}' (ID: {$restaurant->id}). Owner account created for {$user->email}."
                ),
            ]);

            Notification::make()
                ->title('Lead converted successfully')
                ->body("Restaurant '{$restaurant->name}' created. Owner account created for {$user->email} with a secure random password.")
                ->success()
                ->send();
        });

        // Refresh the record to show updated data
        $this->refreshFormData(['status', 'restaurant_id', 'internal_notes']);
    }

    private function appendNote(?string $existing, string $note): string
    {
        $timestamp = now()->format('Y-m-d H:i');
        $newNote = "[{$timestamp}] {$note}";

        if (empty($existing)) {
            return $newNote;
        }

        return $existing . "\n\n" . $newNote;
    }
}
