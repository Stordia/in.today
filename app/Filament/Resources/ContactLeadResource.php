<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContactLeadStatus;
use App\Enums\GlobalRole;
use App\Enums\RestaurantPlan;
use App\Enums\RestaurantRole;
use App\Filament\Resources\ContactLeadResource\Pages;
use App\Models\City;
use App\Models\ContactLead;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactLeadResource extends Resource
{
    protected static ?string $model = ContactLead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Contact Lead';

    protected static ?string $pluralModelLabel = 'Contact Leads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Lead')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Lead Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Contact Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(50),
                                    ])->columns(3),

                                Forms\Components\Section::make('Business Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('restaurant_name')
                                            ->label('Restaurant/Business Name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('type')
                                            ->label('Business Type')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('country')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('website_url')
                                            ->label('Website')
                                            ->url()
                                            ->maxLength(500),
                                    ])->columns(3),

                                Forms\Components\Section::make('Request Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('services_list')
                                            ->label('Services')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('budget')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\Textarea::make('message')
                                            ->rows(4)
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('CRM')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Section::make('Pipeline')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->options(ContactLeadStatus::class)
                                            ->required()
                                            ->default(ContactLeadStatus::New)
                                            ->native(false),
                                        Forms\Components\Select::make('assigned_to_user_id')
                                            ->label('Assigned To')
                                            ->options(
                                                User::query()
                                                    ->where('global_role', GlobalRole::PlatformAdmin)
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->placeholder('Unassigned'),
                                    ])->columns(2),

                                Forms\Components\Section::make('Notes')
                                    ->schema([
                                        Forms\Components\Textarea::make('internal_notes')
                                            ->label('Internal Notes')
                                            ->rows(6)
                                            ->helperText('Private notes about this lead (not visible to the customer)'),
                                    ]),

                                Forms\Components\Section::make('Conversion')
                                    ->schema([
                                        Forms\Components\Placeholder::make('restaurant_link')
                                            ->label('Converted Restaurant')
                                            ->content(fn (ContactLead $record): string => $record->restaurant
                                                ? $record->restaurant->name
                                                : 'Not converted yet'
                                            )
                                            ->visible(fn (?ContactLead $record): bool => $record !== null),
                                    ])
                                    ->visible(fn (?ContactLead $record): bool => $record !== null),
                            ]),

                        Forms\Components\Tabs\Tab::make('Technical')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Source Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('locale')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('source_url')
                                            ->label('Source URL')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('ip_address')
                                            ->label('IP Address')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\Textarea::make('user_agent')
                                            ->label('User Agent')
                                            ->rows(2)
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable(['city', 'country']),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ContactLeadStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('restaurant_id')
                    ->label('Converted')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ContactLeadStatus::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(fn () => ContactLead::query()
                        ->whereNotNull('type')
                        ->distinct()
                        ->pluck('type', 'type')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('assigned_to_user_id')
                    ->label('Assigned To')
                    ->options(
                        User::query()
                            ->where('global_role', GlobalRole::PlatformAdmin)
                            ->pluck('name', 'id')
                    ),
                Tables\Filters\TernaryFilter::make('converted')
                    ->label('Converted')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('restaurant_id'),
                        false: fn ($query) => $query->whereNull('restaurant_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('mark_contacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->visible(fn (ContactLead $record): bool => $record->status === ContactLeadStatus::New)
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Contacted])),
                    Tables\Actions\Action::make('mark_qualified')
                        ->label('Mark as Qualified')
                        ->icon('heroicon-o-star')
                        ->color('purple')
                        ->visible(fn (ContactLead $record): bool => in_array($record->status, [ContactLeadStatus::New, ContactLeadStatus::Contacted]))
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Qualified])),
                    Tables\Actions\Action::make('mark_proposal_sent')
                        ->label('Mark as Proposal Sent')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->visible(fn (ContactLead $record): bool => in_array($record->status, [ContactLeadStatus::Qualified]))
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::ProposalSent])),
                    Tables\Actions\Action::make('mark_won')
                        ->label('Mark as Won')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (ContactLead $record): bool => $record->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Won])),
                    Tables\Actions\Action::make('mark_lost')
                        ->label('Mark as Lost')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (ContactLead $record): bool => $record->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Lost])),
                    Tables\Actions\Action::make('mark_spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-trash')
                        ->color('gray')
                        ->visible(fn (ContactLead $record): bool => $record->status !== ContactLeadStatus::Spam)
                        ->requiresConfirmation()
                        ->action(fn (ContactLead $record) => $record->update(['status' => ContactLeadStatus::Spam])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_spam_bulk')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => ContactLeadStatus::Spam])),
                    Tables\Actions\BulkAction::make('assign_to_me')
                        ->label('Assign to Me')
                        ->icon('heroicon-o-user-plus')
                        ->action(fn ($records) => $records->each->update(['assigned_to_user_id' => Auth::id()])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactLeads::route('/'),
            'create' => Pages\CreateContactLead::route('/create'),
            'edit' => Pages\EditContactLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ContactLead::query()
            ->where('status', ContactLeadStatus::New)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
